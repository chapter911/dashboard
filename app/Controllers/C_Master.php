<?php

namespace App\Controllers;

use App\Models\KategoriTeganganModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class C_Master extends BaseController
{
    private const ALLOWED_KATEGORI = ['TR', 'TM', 'TT'];

    public function kategoriTegangan(): string
    {
        $model = new KategoriTeganganModel();

        try {
            $rows = $model->getKategoriByTarif();
            $tarifOptions = $model->getTarifOptions();
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_LOAD_FAILED: {message}', ['message' => $e->getMessage()]);
            session()->setFlashdata('error', 'Gagal memuat data kategori tegangan.');
            $rows = [];
            $tarifOptions = [];
        }

        return view('master/kategori_tegangan', [
            'title' => 'Kategori Tegangan',
            'pageHeading' => 'Kategori Tegangan',
            'rows' => $rows,
            'tarifOptions' => $tarifOptions,
            'kategoriOptions' => self::ALLOWED_KATEGORI,
        ]);
    }

    public function saveKategoriTegangan(): RedirectResponse
    {
        $rules = [
            'id' => 'permit_empty|integer',
            'tarif' => 'required|max_length[100]',
            'kategori_tegangan' => 'required|in_list[TR,TM,TT]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = (int) ($this->request->getPost('id') ?? 0);
        $tarif = strtoupper(trim((string) $this->request->getPost('tarif')));
        $kategori = strtoupper(trim((string) $this->request->getPost('kategori_tegangan')));

        if ($tarif === '') {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Tarif wajib diisi.');
        }

        if (! in_array($kategori, self::ALLOWED_KATEGORI, true)) {
            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Kategori tegangan tidak valid.');
        }

        $model = new KategoriTeganganModel();
        $payload = [
            'tarif' => $tarif,
            'kategori_tegangan' => $kategori,
            'created_by' => (string) (session('username') ?? 'system'),
        ];

        try {
            if ($id > 0) {
                $existing = $model->find($id);
                if (! is_array($existing)) {
                    return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Data tidak ditemukan.');
                }

                $sameTarif = $model->where('tarif', $tarif)->where('id !=', $id)->first();
                if (is_array($sameTarif)) {
                    return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Tarif sudah digunakan data lain.');
                }

                $model->update($id, $payload);

                return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil diperbarui.');
            }

            $existingByTarif = $model->findByTarif($tarif);
            if (is_array($existingByTarif)) {
                $model->update((int) ($existingByTarif['id'] ?? 0), $payload);

                return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil diperbarui.');
            }

            $model->insert($payload, false);
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_SAVE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Master/KategoriTegangan')->withInput()->with('error', 'Gagal menyimpan kategori tegangan.');
        }

        return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil ditambahkan.');
    }

    public function deleteKategoriTegangan(): RedirectResponse
    {
        $rules = [
            'id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Permintaan hapus tidak valid.');
        }

        $id = (int) $this->request->getPost('id');
        $model = new KategoriTeganganModel();

        try {
            $row = $model->find($id);
            if (! is_array($row)) {
                return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Data tidak ditemukan.');
            }

            $model->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'KATEGORI_TEGANGAN_DELETE_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/C_Master/KategoriTegangan')->with('error', 'Gagal menghapus data kategori tegangan.');
        }

        return redirect()->to('/C_Master/KategoriTegangan')->with('success', 'Kategori tegangan berhasil dihapus.');
    }
}
