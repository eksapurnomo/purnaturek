<?php

namespace App\Support;

use App\Models\CompanySupplyPage;
use App\Models\CompanySupplySection;
use Carbon\Carbon;
use Webkul\User\Models\Admin;

class CompanySupplyManager
{
    /**
     * Get the singleton Company & Supply Information page with its sections.
     */
    public static function getPage(): CompanySupplyPage
    {
        return CompanySupplyPage::getOrCreateDefault()->load('sections');
    }

    /**
     * Update page settings and all section contents.
     */
    public static function updatePage(array $data, ?Admin $admin = null): CompanySupplyPage
    {
        $page = self::getPage();

        $status = $data['status'] ?? $page->status;
        $publishedAt = $page->published_at;

        if ($status === CompanySupplyPage::STATUS_PUBLISHED && ! $page->published_at) {
            $publishedAt = Carbon::now();
        }

        $page->update([
            'title'        => trim($data['title']),
            'description'  => ! empty($data['description']) ? trim($data['description']) : null,
            'status'       => $status,
            'published_at' => $publishedAt,
            'updated_by'   => $admin?->id,
        ]);

        if (! empty($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as $secData) {
                if (empty($secData['id'])) {
                    continue;
                }

                $section = CompanySupplySection::where('page_id', $page->id)->find($secData['id']);
                if ($section) {
                    $section->update([
                        'title'      => trim($secData['title']),
                        'content'    => isset($secData['content']) && trim((string) $secData['content']) !== '' ? trim((string) $secData['content']) : null,
                        'sort_order' => isset($secData['sort_order']) ? (int) $secData['sort_order'] : $section->sort_order,
                        'is_enabled' => ! empty($secData['is_enabled']),
                    ]);
                }
            }
        }

        return $page->fresh(['sections', 'updater']);
    }

    /**
     * Set page status to published.
     */
    public static function publishPage(?Admin $admin = null): CompanySupplyPage
    {
        $page = self::getPage();

        $page->update([
            'status'       => CompanySupplyPage::STATUS_PUBLISHED,
            'published_at' => Carbon::now(),
            'updated_by'   => $admin?->id,
        ]);

        return $page->fresh(['sections']);
    }

    /**
     * Set page status to unpublished.
     */
    public static function unpublishPage(?Admin $admin = null): CompanySupplyPage
    {
        $page = self::getPage();

        $page->update([
            'status'     => CompanySupplyPage::STATUS_UNPUBLISHED,
            'updated_by' => $admin?->id,
        ]);

        return $page->fresh(['sections']);
    }

    /**
     * Format raw user text into safe, beautiful HTML paragraphs and bullet lists with complete XSS protection.
     */
    public static function formatSafeContent(?string $content): string
    {
        if (empty($content) || trim($content) === '') {
            return '';
        }

        // Split into raw blocks by 2 or more newlines
        $blocks = preg_split('/(\r\n\s*|\n\s*){2,}/', trim($content));
        $outputHtml = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            $lines = preg_split('/\r\n|\n/', $block);
            $isList = true;

            // Check if every line in block looks like a bullet item
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (! preg_match('/^(\*|\-|\•|\d+[\.\)])\s+/', $trimmedLine)) {
                    $isList = false;
                    break;
                }
            }

            if ($isList && count($lines) > 0) {
                $listItemsHtml = [];
                foreach ($lines as $line) {
                    $cleaned = preg_replace('/^(\*|\-|\•|\d+[\.\)])\s+/', '', trim($line));
                    $listItemsHtml[] = '<li class="flex items-start gap-2"><span class="text-emerald-600 font-bold">&bull;</span><span>' . e($cleaned) . '</span></li>';
                }
                $outputHtml[] = '<ul class="space-y-1.5 my-3 pl-2 text-sm text-zinc-700 dark:text-zinc-300">' . implode('', $listItemsHtml) . '</ul>';
            } else {
                $escapedLines = array_map(fn ($l) => e(trim($l)), $lines);
                $paragraphContent = implode('<br>', $escapedLines);
                $outputHtml[] = '<p class="mb-4 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">' . $paragraphContent . '</p>';
            }
        }

        return implode("\n", $outputHtml);
    }
}

