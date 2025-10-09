<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        // Gabungkan tanggal dan waktu
        if ($request->due_date && $request->due_time) {
            $validated['due_date'] = Carbon::parse($request->due_date . ' ' . $request->due_time);
        } elseif ($request->due_date) {
            $validated['due_date'] = Carbon::parse($request->due_date)->endOfDay();
        }

        // Tambahkan user_id dari user yang sedang login
        $validated['user_id'] = Auth::id();

        // Simpan task ke database dan ambil hasilnya
        $task = Task::create($validated);

        // 🔔 Buat notifikasi tugas baru
        NotificationController::createNewTaskNotification($task);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Simpan status lama sebelum update
        $oldStatus = $task->status;

        // Cek apakah update status saja (misalnya dari tombol toggle)
        if ($request->has('status') && !$request->has('title')) {
            $validated = $request->validate([
                'status' => 'required|in:pending,done',
            ]);

            $task->update($validated);

            // 🔔 Buat notifikasi jika status berubah jadi "done"
            if ($oldStatus === 'pending' && $validated['status'] === 'done') {
                NotificationController::createTaskCompletedNotification($task);
            }

            return redirect()->route('tasks.index')->with('success', 'Status tugas berhasil diubah!');
        } else {
            // Update data lengkap (dari form edit)
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'due_time' => 'nullable|date_format:H:i',
                'status' => 'required|in:pending,done',
            ]);

            // Gabungkan tanggal dan waktu
            if ($request->due_date && $request->due_time) {
                $validated['due_date'] = Carbon::parse($request->due_date . ' ' . $request->due_time);
            } elseif ($request->due_date) {
                $validated['due_date'] = Carbon::parse($request->due_date)->endOfDay();
            } else {
                $validated['due_date'] = null;
            }

            unset($validated['due_time']);

            $task->update($validated);

            // 🔔 Buat notifikasi jika status berubah jadi "done"
            if ($oldStatus === 'pending' && $validated['status'] === 'done') {
                NotificationController::createTaskCompletedNotification($task);
            }

            return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        \Log::info('DEBUG: Memulai proses hapus task ' . $task->title);

        try {
            \App\Models\Notification::create([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'type' => 'task_deleted',
                'title' => '🗑️ Tugas Dihapus!',
                'message' => "Tugas '{$task->title}' telah dihapus dari daftar tugas.",
            ]);

            \Log::info('DEBUG: Notifikasi berhasil dibuat');
        } catch (\Exception $e) {
            \Log::error('GAGAL buat notifikasi: ' . $e->getMessage());
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * Export tasks to Excel (CSV format) - DENGAN JAM
     */
    public function exportToExcel()
    {
        $tasks = Task::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        $filename = 'daftar-tugas-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk UTF-8 support di Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom
            fputcsv($file, [
                'No',
                'Judul Tugas',
                'Deskripsi',
                'Status',
                'Deadline',
                'Waktu Deadline',
                'Dibuat Pada',
                'Diupdate Pada',
                'Keterangan Deadline'
            ]);

            // Data rows
            $no = 1;
            foreach ($tasks as $task) {
                $status = $task->status == 'done' ? 'Selesai' : 'Pending';
                $deadline = $task->due_date ? $task->due_date->format('d/m/Y') : 'Tidak ada deadline';
                $deadlineTime = $task->due_date ? $task->due_date->format('H:i') : '-';
                $created = $task->created_at->format('d/m/Y H:i');
                $updated = $task->updated_at->format('d/m/Y H:i');
                
                // Keterangan deadline
                $deadlineNote = 'Tidak ada deadline';
                if ($task->due_date) {
                    $now = now();
                    $diffInDays = $now->diffInDays($task->due_date, false);
                    
                    if ($task->status == 'done') {
                        $deadlineNote = 'Selesai';
                    } elseif ($diffInDays < 0) {
                        $deadlineNote = 'Terlambat ' . abs($diffInDays) . ' hari';
                    } elseif ($diffInDays <= 1) {
                        $hoursLeft = $now->diffInHours($task->due_date, false);
                        if ($hoursLeft <= 0) {
                            $deadlineNote = 'Terlambat ' . abs($hoursLeft) . ' jam';
                        } else {
                            $deadlineNote = 'Deadline Dekat (Kurang dari 24 jam)';
                        }
                    } else {
                        $deadlineNote = $diffInDays . ' hari menuju deadline';
                    }
                }
                
                fputcsv($file, [
                    $no++,
                    $task->title,
                    $task->description ?? 'Tidak ada deskripsi',
                    $status,
                    $deadline,
                    $deadlineTime,
                    $created,
                    $updated,
                    $deadlineNote
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export tasks to Google Sheets (TSV format) - DENGAN JAM
     */
    public function exportToGoogleSheets()
    {
        $tasks = Task::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        // Generate TSV (Tab-Separated Values) untuk Google Sheets
        $filename = 'daftar-tugas-' . date('Y-m-d-His') . '.tsv';
        
        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom (Tab-separated)
            fwrite($file, implode("\t", [
                'No',
                'Judul Tugas',
                'Deskripsi',
                'Status',
                'Deadline',
                'Waktu Deadline',
                'Dibuat Pada',
                'Diupdate Pada',
                'Keterangan Deadline'
            ]) . "\n");

            // Data rows
            $no = 1;
            foreach ($tasks as $task) {
                $status = $task->status == 'done' ? 'Selesai' : 'Pending';
                $deadline = $task->due_date ? $task->due_date->format('d/m/Y') : 'Tidak ada deadline';
                $deadlineTime = $task->due_date ? $task->due_date->format('H:i') : '-';
                $created = $task->created_at->format('d/m/Y H:i');
                $updated = $task->updated_at->format('d/m/Y H:i');
                
                // Keterangan deadline
                $deadlineNote = 'Tidak ada deadline';
                if ($task->due_date) {
                    $now = now();
                    $diffInDays = $now->diffInDays($task->due_date, false);
                    
                    if ($task->status == 'done') {
                        $deadlineNote = 'Selesai';
                    } elseif ($diffInDays < 0) {
                        $deadlineNote = 'Terlambat ' . abs($diffInDays) . ' hari';
                    } elseif ($diffInDays <= 1) {
                        $hoursLeft = $now->diffInHours($task->due_date, false);
                        if ($hoursLeft <= 0) {
                            $deadlineNote = 'Terlambat ' . abs($hoursLeft) . ' jam';
                        } else {
                            $deadlineNote = 'Deadline Dekat (Kurang dari 24 jam)';
                        }
                    } else {
                        $deadlineNote = $diffInDays . ' hari menuju deadline';
                    }
                }
                
                fwrite($file, implode("\t", [
                    $no++,
                    str_replace(["\t", "\n", "\r"], ' ', $task->title),
                    str_replace(["\t", "\n", "\r"], ' ', $task->description ?? 'Tidak ada deskripsi'),
                    $status,
                    $deadline,
                    $deadlineTime,
                    $created,
                    $updated,
                    $deadlineNote
                ]) . "\n");
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('tasks.import');
    }

    /**
     * Process import from file or URL - SUPPORT KEDUANYA SEKALIGUS
     */
    public function import(Request $request)
    {
        // Validasi: keduanya opsional tapi minimal salah satu harus ada
        $validator = Validator::make($request->all(), [
            'import_file' => 'nullable|file|mimes:csv,txt,xlsx,xls|max:10240',
            'import_url' => 'nullable|url',
        ], [
            'import_file.mimes' => 'Format file harus CSV, Excel, atau TXT',
            'import_file.max' => 'File tidak boleh lebih dari 10MB',
            'import_url.url' => 'URL harus valid',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Pastikan minimal salah satu diisi
        if (!$request->hasFile('import_file') && !$request->filled('import_url')) {
            return back()->with('error', 'Silakan upload file atau masukkan URL untuk import.');
        }

        try {
            $totalImported = 0;
            $allErrors = [];

            \Log::info("=== MULAI PROSES IMPORT ===");

            // ✅ Import dari FILE (jika ada)
            if ($request->hasFile('import_file')) {
                $file = $request->file('import_file');
                \Log::info("Importing from FILE: " . $file->getClientOriginalName());

                $result = $this->importFromFile($file);
                $totalImported += $result['imported'];
                $allErrors = array_merge($allErrors, $result['errors']);
                
                \Log::info("File import: {$result['imported']} tasks imported");
            }

            // ✅ Import dari URL (jika ada)
            if ($request->filled('import_url')) {
                $urlString = $request->input('import_url');
                \Log::info("Importing from URL: " . $urlString);

                $result = $this->processImportFromUrl($urlString);
                $totalImported += $result['imported'];
                $allErrors = array_merge($allErrors, $result['errors']);
                
                \Log::info("URL import: {$result['imported']} tasks imported");
            }

            \Log::info("Import completed: {$totalImported} total imported, " . count($allErrors) . " total errors");

            // Response berdasarkan hasil
            if ($totalImported > 0) {
                $message = "Berhasil mengimport {$totalImported} tugas!";
                
                if (!empty($allErrors)) {
                    $message .= " Terdapat " . count($allErrors) . " error.";
                    \Log::warning("Import errors: ", $allErrors);
                }
                
                return redirect()->route('tasks.index')->with('success', $message);
            } else {
                $errorMessage = 'Tidak ada data yang berhasil diimport.';
                if (!empty($allErrors)) {
                    $errorMessage .= ' Error: ' . implode(', ', array_slice($allErrors, 0, 3));
                }
                return back()->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            \Log::error("Import process failed: " . $e->getMessage());
            return back()->with('error', 'Terjadi error: ' . $e->getMessage());
        }
    }

    /**
     * Import from uploaded file
     */
    private function importFromFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        
        if (in_array($extension, ['csv', 'txt'])) {
            $result = $this->processCSV($file->getPathname());
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $result = $this->processExcel($file->getPathname());
        } else {
            throw new \Exception('Format file tidak didukung');
        }

        return $result;
    }

    /**
     * Import from URL (Google Sheets, etc) - COMPREHENSIVE VERSION
     */
    private function processImportFromUrl(string $url)
    {
        // Jika URL Google Sheets biasa, ubah ke export CSV
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([A-Za-z0-9_-]+)\/.*$/', $url, $matches)) {
            $sheetId = $matches[1];
            $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv";
        }

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception('Gagal mengakses URL. Status: ' . $response->status());
        }

        $content = $response->body();
        
        // Kalau masih mengandung HTML, itu berarti bukan file mentah
        if (stripos($content, '<html') !== false || stripos($content, '<!DOCTYPE') !== false) {
            throw new \Exception('URL tidak valid. Harus URL file CSV/TSV mentah.');
        }

        // Deteksi separator
        $separator = ',';
        if (strpos($content, "\t") !== false) {
            $separator = "\t";
        } elseif (strpos($content, ';') !== false) {
            $separator = ';';
        }

        return $this->processCSVContentWithSeparator($content, $separator);
    }

    /**
     * Process CSV content dengan separator custom
     */
    private function processCSVContentWithSeparator($content, $separator = ',')
    {
        $imported = 0;
        $errors = [];
        $rowNumber = 0;

        $lines = explode("\n", trim($content));
        
        // Skip header jika ada
        if (count($lines) > 0) {
            $firstLine = $lines[0];
            // Cek jika baris pertama adalah header (mengandung JudulTugas atau kolom lain)
            if (strpos($firstLine, 'JudulTugas') !== false || strpos($firstLine, 'Judul') !== false) {
                array_shift($lines);
                \Log::info("Skipped header row");
            }
        }
        
        foreach ($lines as $line) {
            $rowNumber++;
            
            // Skip baris kosong
            if (empty(trim($line))) {
                continue;
            }
            
            // Handle separator
            if ($separator === ';') {
                $data = str_getcsv($line, ';');
            } else {
                $data = str_getcsv($line);
            }
            
            \Log::info("URL Import - Row {$rowNumber}: ", $data);
            
            $result = $this->createTaskFromRow($data, $rowNumber, 'url');
            if ($result['success']) {
                $imported++;
            } else {
                $errors[] = $result['error'];
            }
        }

        \Log::info("URL import result: {$imported} imported, " . count($errors) . " errors");
        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Process CSV file dengan handle separator titik koma
     */
    private function processCSV($filePath)
    {
        $imported = 0;
        $errors = [];
        $rowNumber = 0;

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            // Baca header untuk deteksi separator
            $firstLine = fgets($handle);
            $separator = (strpos($firstLine, ';') !== false) ? ';' : ',';
            
            // Reset pointer ke awal
            fseek($handle, 0);
            
            // Skip header row
            $headers = fgetcsv($handle, 0, $separator);
            \Log::info("CSV Headers dengan separator '{$separator}': ", $headers);
            
            while (($data = fgetcsv($handle, 0, $separator)) !== FALSE) {
                $rowNumber++;
                \Log::info("Row {$rowNumber} data: ", $data);
                
                $result = $this->createTaskFromRow($data, $rowNumber, 'file');
                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = $result['error'];
                }
            }
            fclose($handle);
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Process Excel file (menggunakan maatwebsite/excel)
     */
    private function processExcel($filePath)
    {
        $imported = 0;
        $errors = [];

        try {
            // Check if package is installed
            if (!class_exists('Maatwebsite\Excel\Facades\Excel')) {
                throw new \Exception('Package maatwebsite/excel belum diinstall. Jalankan: composer require maatwebsite/excel');
            }

            $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $filePath);
            
            if (empty($data[0])) {
                return ['imported' => 0, 'errors' => ['File Excel kosong']];
            }

            $rows = $data[0];
            // Skip header
            array_shift($rows);

            $rowNumber = 0;
            foreach ($rows as $row) {
                $rowNumber++;
                $result = $this->createTaskFromRow($row, $rowNumber, 'excel');
                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = $result['error'];
                }
            }

        } catch (\Exception $e) {
            throw new \Exception('Error membaca file Excel: ' . $e->getMessage());
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Create task from data row - FIXED VERSION untuk format Indonesia
     */
    private function createTaskFromRow($data, $rowNumber, $source = 'unknown')
    {
        try {
            \Log::info("[{$source}] Raw data row {$rowNumber}: ", $data);

            // Skip jika baris kosong
            if (count(array_filter($data)) === 0) {
                return ['success' => false, 'error' => "[{$source}] Baris {$rowNumber}: Baris kosong"];
            }

            // Mapping kolom tetap berdasarkan template
            $title = isset($data[0]) ? trim($data[0]) : '';
            $description = isset($data[1]) ? trim($data[1]) : null;
            $status = isset($data[2]) ? trim($data[2]) : 'pending';
            $dueDateStr = isset($data[3]) ? trim($data[3]) : null;
            $dueTimeStr = isset($data[4]) ? trim($data[4]) : null;

            \Log::info("[{$source}] Parsed basic data - Title: {$title}, DueDate: '{$dueDateStr}', DueTime: '{$dueTimeStr}'");

            // Validasi judul
            if (empty($title)) {
                return ['success' => false, 'error' => "[{$source}] Baris {$rowNumber}: Judul tugas tidak boleh kosong"];
            }

            // Normalize status
            $status = in_array(strtolower($status), ['done', 'selesai', 'completed']) ? 'done' : 'pending';

            // Parse tanggal - FIXED untuk format Indonesia
            $dueDate = null;
            if (!empty($dueDateStr)) {
                \Log::info("[{$source}] Attempting to parse date: '{$dueDateStr}'");
                
                try {
                    // Format DD/MM/YYYY (format Indonesia)
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dueDateStr, $matches)) {
                        $day = $matches[1];
                        $month = $matches[2];
                        $year = $matches[3];
                        
                        if (checkdate($month, $day, $year)) {
                            $dueDate = Carbon::createFromDate($year, $month, $day);
                            
                            // Tambahkan jam jika ada
                            if (!empty($dueTimeStr) && preg_match('/^(\d{1,2}):(\d{2})$/', $dueTimeStr, $timeMatches)) {
                                $hour = $timeMatches[1];
                                $minute = $timeMatches[2];
                                $dueDate->setTime($hour, $minute);
                                \Log::info("[{$source}] Successfully parsed as DD/MM/YYYY with time: " . $dueDate->format('Y-m-d H:i:s'));
                            } else {
                                \Log::info("[{$source}] Successfully parsed as DD/MM/YYYY: " . $dueDate->format('Y-m-d'));
                            }
                        } else {
                            \Log::warning("[{$source}] Invalid date: {$day}/{$month}/{$year}");
                        }
                    }
                    // Format YYYY-MM-DD
                    elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dueDateStr, $matches)) {
                        $year = $matches[1];
                        $month = $matches[2];
                        $day = $matches[3];
                        
                        if (checkdate($month, $day, $year)) {
                            $dueDate = Carbon::createFromDate($year, $month, $day);
                            
                            // Tambahkan jam jika ada
                            if (!empty($dueTimeStr) && preg_match('/^(\d{1,2}):(\d{2})$/', $dueTimeStr, $timeMatches)) {
                                $hour = $timeMatches[1];
                                $minute = $timeMatches[2];
                                $dueDate->setTime($hour, $minute);
                                \Log::info("[{$source}] Successfully parsed as YYYY-MM-DD with time: " . $dueDate->format('Y-m-d H:i:s'));
                            } else {
                                \Log::info("[{$source}] Successfully parsed as YYYY-MM-DD: " . $dueDate->format('Y-m-d'));
                            }
                        }
                    }
                    // Coba parsing umum
                    elseif (strtotime($dueDateStr)) {
                        $dueDate = Carbon::parse($dueDateStr);
                        
                        // Tambahkan jam jika ada
                        if (!empty($dueTimeStr) && preg_match('/^(\d{1,2}):(\d{2})$/', $dueTimeStr, $timeMatches)) {
                            $hour = $timeMatches[1];
                            $minute = $timeMatches[2];
                            $dueDate->setTime($hour, $minute);
                            \Log::info("[{$source}] Successfully parsed with strtotime + time: " . $dueDate->format('Y-m-d H:i:s'));
                        } else {
                            \Log::info("[{$source}] Successfully parsed with strtotime: " . $dueDate->format('Y-m-d'));
                        }
                    } else {
                        \Log::warning("[{$source}] Cannot parse date: '{$dueDateStr}'");
                    }
                } catch (\Exception $e) {
                    \Log::error("[{$source}] Date parsing failed for '{$dueDateStr}': " . $e->getMessage());
                }
            } else {
                \Log::info("[{$source}] Due date is empty for row {$rowNumber}");
            }

            \Log::info("[{$source}] Final task data for row {$rowNumber}:", [
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'due_date' => $dueDate ? $dueDate->format('Y-m-d H:i:s') : 'NULL',
            ]);

            // Create task
            $taskData = [
                'user_id' => Auth::id(),
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'due_date' => $dueDate,
            ];

            $task = Task::create($taskData);

            // Buat notifikasi
            NotificationController::createNewTaskNotification($task);

            return ['success' => true];

        } catch (\Exception $e) {
            \Log::error("[{$source}] Error in row {$rowNumber}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => "[{$source}] Baris {$rowNumber}: " . $e->getMessage()
            ];
        }
    }

    /**
     * Download template import dengan format Indonesia + Jam
     */
    public function downloadTemplate()
    {
        $filename = 'template-import-tugas.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header dengan kolom yang diperlukan untuk import
            fputcsv($file, [
                'JudulTugas',
                'Deskripsi', 
                'Status',
                'Deadline',
                'WaktuDeadline'
            ], ';');

            // Contoh data dengan jam
            fputcsv($file, [
                'Meeting dengan client',
                'Presentasi progress proyek quarterly',
                'Pending',
                '15/01/2024',
                '14:30'
            ], ';');

            fputcsv($file, [
                'Belanja bulanan',
                'Beli bahan makanan dan kebutuhan rumah tangga',
                'Pending', 
                '10/01/2024',
                '09:00'
            ], ';');

            fputcsv($file, [
                'Laporan keuangan',
                'Selesaikan laporan keuangan bulanan',
                'Selesai',
                '05/01/2024',
                '17:00'
            ], ';');

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}