<?php

namespace App\Jobs;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\AuthorRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessArticleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private array $articleData,
        private int $sourceId,
        private ?ArticleRepositoryInterface $articleRepository = null,
        private ?AuthorRepositoryInterface $authorRepository = null,
        private ?CategoryRepositoryInterface $categoryRepository = null,
        private ?SourceRepositoryInterface $sourceRepository = null
    ) {
        $this->onQueue('process-articles');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Resolve repositories from container if not injected
        $this->articleRepository ??= app(ArticleRepositoryInterface::class);
        $this->authorRepository ??= app(AuthorRepositoryInterface::class);
        $this->categoryRepository ??= app(CategoryRepositoryInterface::class);
        $this->sourceRepository ??= app(SourceRepositoryInterface::class);

        try {
            DB::transaction(function () {
                $this->processArticle();
            });
        } catch (\Exception $e) {
            Log::error('Failed to process article', [
                'article_data' => $this->articleData,
                'source_id' => $this->sourceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function processArticle(): void
    {
        $existingArticle = $this->articleRepository->findBySourceAndSourceArticleId(
            $this->sourceId,
            $this->articleData['source_article_id']
        );

        if ($existingArticle) {
            Log::info('Article already exists, skipping', [
                'source_article_id' => $this->articleData['source_article_id'],
                'source_id' => $this->sourceId
            ]);
            return;
        }

        $author = null;
        if (!empty($this->articleData['author'])) {
            $canonicalName = Str::slug($this->articleData['author']);
            $author = $this->authorRepository->findOrCreateByCanonicalName(
                $canonicalName,
                $this->articleData['author']
            );
        }

        $category = null;
        if (!empty($this->articleData['category'])) {
            $slug = Str::slug($this->articleData['category']);
            $category = $this->categoryRepository->findOrCreateBySlug(
                $slug,
                $this->articleData['category']
            );
        }

        $article = $this->articleRepository->create([
            'title' => $this->articleData['title'],
            'slug' => Str::slug($this->articleData['title']),
            'excerpt' => $this->articleData['excerpt'],
            'content' => $this->sanitizeContent($this->articleData['content']),
            'url' => $this->articleData['url'],
            'image_url' => $this->articleData['image_url'],
            'published_at' => $this->articleData['published_at'],
            'source_id' => $this->sourceId,
            'source_article_id' => $this->articleData['source_article_id'],
            'author_id' => $author?->id,
            'category_id' => $category?->id,
            'scraped_at' => now(),
            'raw_payload' => $this->articleData['raw_payload']
        ]);

        Log::info('Article processed successfully', [
            'article_id' => $article->id,
            'title' => $article->title
        ]);
    }

    protected function sanitizeContent(string $content): string
    {
        // Basic HTML sanitization
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');

        // Remove any remaining script tags
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        return trim($content);
    }
}
