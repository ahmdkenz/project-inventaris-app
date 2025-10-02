<?php

namespace App\Services;

use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Carbon;

class FirebaseService
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        // Log untuk memastikan kredensial terbaca
        logger('Firebase credentials path: ' . config('services.firebase.credentials'));
        logger('Firebase database URL: ' . config('services.firebase.database_url'));
    }

    /**
     * Simpan data ke Firebase Realtime Database
     *
     * @param string $path Path di Firebase Realtime Database
     * @param string $key Key (opsional)
     * @param array $data Data yang akan disimpan
     * @return array Data yang disimpan termasuk key
     */
    public function saveData(string $path, array $data, string $key = null)
    {
        $reference = $this->database->getReference($path);

        // Format tanggal ke format Firebase
        $data = $this->formatDates($data);

        if ($key) {
            $reference->getChild($key)->set($data);
            $data['key'] = $key;
        } else {
            $newRef = $reference->push($data);
            $data['key'] = $newRef->getKey();
        }

        return $data;
    }

    /**
     * Mendapatkan data dari Firebase Realtime Database
     *
     * @param string $path Path di Firebase Realtime Database
     * @param string|null $key Key (jika null, mengambil semua data)
     * @return array|null Data dari Firebase
     */
    public function getData(string $path, string $key = null)
    {
        $reference = $this->database->getReference($path);

        if ($key) {
            $snapshot = $reference->getChild($key)->getSnapshot();
            if ($snapshot->exists()) {
                $data = $snapshot->getValue();
                if (is_array($data)) {
                    $data['key'] = $key;
                    return $data;
                }
                return ['value' => $data, 'key' => $key];
            }
            return null;
        } else {
            $snapshot = $reference->getSnapshot();
            if (!$snapshot->exists()) {
                return [];
            }
            
            $data = [];
            $value = $snapshot->getValue();
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    if (is_array($childValue)) {
                        $childValue['key'] = $childKey;
                        $data[] = $childValue;
                    } else {
                        $data[] = ['value' => $childValue, 'key' => $childKey];
                    }
                }
            }
            return $data;
        }
    }

    /**
     * Perbarui data di Firebase Realtime Database
     *
     * @param string $path Path di Firebase Realtime Database
     * @param string $key Key
     * @param array $data Data yang akan diperbarui
     * @return bool Sukses atau tidak
     */
    public function updateData(string $path, string $key, array $data)
    {
        // Format tanggal ke format Firebase
        $data = $this->formatDates($data);
        
        try {
            $this->database->getReference("$path/$key")->update($data);
            return true;
        } catch (\Exception $e) {
            logger("Firebase update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus data di Firebase Realtime Database
     *
     * @param string $path Path di Firebase Realtime Database
     * @param string $key Key
     * @return bool Sukses atau tidak
     */
    public function deleteData(string $path, string $key)
    {
        try {
            $this->database->getReference("$path/$key")->remove();
            return true;
        } catch (\Exception $e) {
            logger("Firebase delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format tanggal Carbon ke format ISO string untuk Realtime Database
     *
     * @param array $data Data dengan kemungkinan objek tanggal Carbon
     * @return array Data dengan tanggal yang sudah diformat
     */
    private function formatDates(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Carbon) {
                $data[$key] = $value->toIso8601String();
            } elseif (is_array($value)) {
                $data[$key] = $this->formatDates($value);
            }
        }
        return $data;
    }
    
    /**
     * Sinkronkan data dari database SQL ke Firebase
     *
     * @param string $table Nama tabel SQL
     * @param string $path Path di Firebase
     * @param array $records Data record dari SQL
     * @return bool Sukses atau tidak
     */
    public function syncFromSQL(string $table, string $path, array $records)
    {
        try {
            // Hapus data lama di path tersebut
            $this->database->getReference($path)->remove();
            
            // Masukkan data baru
            foreach ($records as $record) {
                $id = $record['id'] ?? null;
                if ($id) {
                    $this->saveData($path, $record, $id);
                } else {
                    $this->saveData($path, $record);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            logger("Firebase sync error for $table: " . $e->getMessage());
            return false;
        }
    }
}