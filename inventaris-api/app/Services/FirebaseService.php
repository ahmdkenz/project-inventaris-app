<?php

namespace App\Services;

use Kreait\Firebase\Contract\Firestore;
use Illuminate\Support\Carbon;

class FirebaseService
{
    protected $firestore;

    public function __construct(Firestore $firestore)
    {
        $this->firestore = $firestore;

        // Log untuk memastikan kredensial dan project_id terbaca
        logger('Firebase credentials path: ' . config('services.firebase.credentials'));
        logger('Firebase project ID: ' . config('services.firebase.project_id'));
    }

    /**
     * Simpan data ke Firebase
     *
     * @param string $collection Nama koleksi di Firebase
     * @param string $documentId ID dokumen (opsional)
     * @param array $data Data yang akan disimpan
     * @return array Data yang disimpan termasuk ID
     */
    public function saveData(string $collection, array $data, string $documentId = null)
    {
        $db = $this->firestore->database();
        $collectionRef = $db->collection($collection);

        // Format tanggal ke format Firebase
        $data = $this->formatDates($data);

        if ($documentId) {
            $collectionRef->document($documentId)->set($data);
            $data['id'] = $documentId;
        } else {
            $docRef = $collectionRef->add($data);
            $data['id'] = $docRef->id();
        }

        return $data;
    }

    /**
     * Mendapatkan data dari Firebase
     *
     * @param string $collection Nama koleksi
     * @param string|null $documentId ID dokumen (jika null, mengambil semua dokumen)
     * @return array Data dari Firebase
     */
    public function getData(string $collection, string $documentId = null)
    {
        $db = $this->firestore->database();
        $collectionRef = $db->collection($collection);

        if ($documentId) {
            $snapshot = $collectionRef->document($documentId)->snapshot();
            if ($snapshot->exists()) {
                $data = $snapshot->data();
                $data['id'] = $snapshot->id();
                return $data;
            }
            return null;
        } else {
            $documents = $collectionRef->documents();
            $data = [];
            foreach ($documents as $document) {
                $item = $document->data();
                $item['id'] = $document->id();
                $data[] = $item;
            }
            return $data;
        }
    }

    /**
     * Perbarui data di Firebase
     *
     * @param string $collection Nama koleksi
     * @param string $documentId ID dokumen
     * @param array $data Data yang akan diperbarui
     * @return bool Sukses atau tidak
     */
    public function updateData(string $collection, string $documentId, array $data)
    {
        $db = $this->firestore->database();
        
        // Format tanggal ke format Firebase
        $data = $this->formatDates($data);
        
        try {
            $db->collection($collection)->document($documentId)->update($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Hapus data di Firebase
     *
     * @param string $collection Nama koleksi
     * @param string $documentId ID dokumen
     * @return bool Sukses atau tidak
     */
    public function deleteData(string $collection, string $documentId)
    {
        $db = $this->firestore->database();
        try {
            $db->collection($collection)->document($documentId)->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format tanggal Carbon ke format Timestamp Firebase
     *
     * @param array $data Data dengan kemungkinan objek tanggal Carbon
     * @return array Data dengan tanggal yang sudah diformat
     */
    private function formatDates(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Carbon) {
                $data[$key] = ['seconds' => $value->timestamp, 'nanoseconds' => 0];
            } elseif (is_array($value)) {
                $data[$key] = $this->formatDates($value);
            }
        }
        return $data;
    }
}