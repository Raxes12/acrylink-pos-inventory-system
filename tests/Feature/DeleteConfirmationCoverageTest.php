<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class DeleteConfirmationCoverageTest extends TestCase
{
    public function test_every_blade_delete_form_uses_the_shared_confirmation_dialog(): void
    {
        $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
        $deleteForms = [];

        foreach ($views as $view) {
            if (! $view->isFile() || $view->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($view->getPathname());
            preg_match_all('/<form\b.*?<\/form>/si', $contents, $forms);

            foreach ($forms[0] as $form) {
                if (! preg_match('/@method\s*\(\s*[\'\"]DELETE[\'\"]\s*\)/i', $form)) {
                    continue;
                }

                $deleteForms[] = $view->getPathname();
                $this->assertStringContainsString(
                    'data-confirm',
                    $form,
                    "Delete form in {$view->getPathname()} must use the shared confirmation dialog."
                );
            }
        }

        $this->assertNotEmpty($deleteForms, 'The confirmation audit did not find any delete forms.');
    }

    public function test_shared_dialog_automatically_guards_delete_forms_and_defaults_to_cancel(): void
    {
        $script = file_get_contents(resource_path('js/views/layouts-app-1.js'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString("form.hasAttribute('data-confirm') || isDeleteForm(form)", $script);
        $this->assertStringContainsString('input[name="_method"]', $script);
        $this->assertStringContainsString('confirmCancel?.focus()', $script);
        $this->assertStringContainsString('role="alertdialog"', $layout);
        $this->assertLessThan(
            strpos($layout, 'id="confirmActionSubmit"'),
            strpos($layout, 'id="confirmActionCancel"'),
            'Cancel should appear before the destructive confirmation action.'
        );
    }
}
