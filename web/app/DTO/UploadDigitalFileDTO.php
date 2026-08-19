<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UploadDigitalFileDTO
{
    /**
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'zip',
        'rar',
        '7z',
        'txt',
        'csv',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'mp3',
        'mp4',
        'epub',
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    public function __construct(
        public UploadedFile $file
    ) {}

    public static function fromRequest(Request $request): self
    {
        $file = $request->file('file');

        if ($file instanceof UploadedFile && ! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => [self::uploadErrorMessage($file)],
            ]);
        }

        $validated = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:51200',
                'extensions:'.implode(',', self::ALLOWED_EXTENSIONS),
            ],
        ], [
            'file.required' => 'Pilih file digital terlebih dahulu.',
            'file.file' => 'Upload harus berupa file.',
            'file.max' => 'Ukuran file maksimal 50MB (atau di bawah batas upload PHP).',
            'file.extensions' => 'Ekstensi file tidak didukung. Gunakan: '.implode(', ', self::ALLOWED_EXTENSIONS).'.',
        ])->validate();

        return new self($validated['file']);
    }

    protected static function uploadErrorMessage(UploadedFile $file): string
    {
        $phpMax = ini_get('upload_max_filesize') ?: '2M';

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File terlalu besar untuk batas PHP saat ini (upload_max_filesize={$phpMax}). Naikkan ke minimal 50M di php.ini.",
            UPLOAD_ERR_PARTIAL => 'Upload file terputus. Coba lagi.',
            UPLOAD_ERR_NO_FILE => 'Pilih file digital terlebih dahulu.',
            default => 'Upload file gagal (kode '.$file->getError().').',
        };
    }
}
