<?php

namespace App\Services;

use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * Markdown 解析服务
 *
 * 将用户输入的 Markdown 转换为安全的 HTML：
 *  - 使用 league/commonmark 的 GitHub 风格 Markdown
 *  - 允许图片标签（用于发帖/回复插图）
 *  - 禁用原生 HTML 标签（防止 XSS），只允许通过 Markdown 语法生成的标签
 */
class MarkdownService
{
    private static ?GithubFlavoredMarkdownConverter $converter = null;

    /**
     * 将 Markdown 文本转换为安全的 HTML
     */
    public static function toHtml(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        $html = self::converter()->convert($markdown);

        // 二次过滤：去掉可能的恶意脚本（league/commonmark 默认已转义原生 HTML，
        // 这里再保险地清理一次 javascript: 协议和 on* 事件属性）
        $html = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);

        return (string) $html;
    }

    private static function converter(): GithubFlavoredMarkdownConverter
    {
        if (self::$converter === null) {
            self::$converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'escape',   // 转义原生 HTML，防止 XSS
                'allow_unsafe_links' => false, // 禁止 javascript: 等不安全链接
                'max_nesting_level' => 20,
            ]);
        }
        return self::$converter;
    }
}
