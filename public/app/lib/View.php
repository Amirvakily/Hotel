<?php
/**
 * Plain PHP templates in app/views/.
 */
final class View
{
    public static function render(string $tpl, array $data = [], ?string $layout = 'front/layout'): string
    {
        $content = self::partial($tpl, $data);
        if ($layout) {
            $content = self::partial($layout, $data + ['content' => $content]);
        }
        return $content;
    }

    public static function partial(string $__tpl, array $__data = []): string
    {
        $__file = APP . '/views/' . $__tpl . '.php';
        if (!is_file($__file)) {
            throw new RuntimeException('Missing view: ' . $__tpl);
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string)ob_get_clean();
    }
}
