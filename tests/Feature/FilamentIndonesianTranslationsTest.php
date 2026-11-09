<?php

namespace Tests\Feature;

use Tests\TestCase;

class FilamentIndonesianTranslationsTest extends TestCase
{
    public function test_crud_actions_and_notifications_use_indonesian_while_edit_label_stays_unchanged(): void
    {
        $this->assertSame('Simpan', __('filament-panels::resources/pages/create-record.form.actions.create.label'));
        $this->assertSame('Simpan perubahan', __('filament-panels::resources/pages/edit-record.form.actions.save.label'));
        $this->assertSame('Tambah pencuci', __('filament-actions::create.single.label', ['label' => 'pencuci']));
        $this->assertSame('Hapus', __('filament-actions::delete.single.label'));
        $this->assertSame('Edit', __('filament-actions::edit.single.label'));

        $this->assertSame('Data berhasil ditambahkan', __('filament-actions::create.single.notifications.created.title'));
        $this->assertSame('Perubahan berhasil disimpan', __('filament-actions::edit.single.notifications.saved.title'));
        $this->assertSame('Data berhasil dihapus', __('filament-actions::delete.single.notifications.deleted.title'));
        $this->assertSame('Data gagal dihapus', __('filament-actions::delete.multiple.notifications.deleted_none.title'));
    }
}
