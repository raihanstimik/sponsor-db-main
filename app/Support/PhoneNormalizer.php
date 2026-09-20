<?php

namespace App\Support;

class PhoneNormalizer
{
    /**
     * Normalisasi nomor HP Indonesia ke format polos 628xxxxxxxxxx:
     * buang tanda +, spasi, dash, kurung, titik, dan 0 di depan;
     * perbaiki kode 6208.../620... menjadi 628...
     * (lihat prd.md §4.3 & project-constitution.md §5).
     */
    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';
        // Buang 0 di depan (mis. 0811, 06208) sebelum koreksi 620 -> 62
        $digits = ltrim($digits, '0');

        if (str_starts_with($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        }

        if (! str_starts_with($digits, '62') && str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    /**
     * Pola HP Indonesia yang valid: 628 + 7-10 digit (total 10-13 digit).
     */
    public static function isValid(string $normalized): bool
    {
        return preg_match('/^628\d{7,10}$/', $normalized) === 1;
    }

    /**
     * Cek apakah nomor merupakan nomor HP valid (628...).
     */
    public static function isMobile(?string $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return self::isValid(self::normalize($value));
    }

    /**
     * Deteksi apakah nomor merupakan telepon kantor / PSTN / fixed-line (kode area Indonesia).
     * Contoh: 021/6221 (Jakarta), 022/6222 (Bandung), 031/6231 (Surabaya), 024/6224 (Semarang), dll.
     */
    public static function isLandline(?string $value): bool
    {
        if (blank($value) || self::isMobile($value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', (string) $value) ?? '';
        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        // Kode area Indonesia: 02x, 03x, 04x, 05x, 07x, 09x (selain 08x)
        if (preg_match('/^0(2[1-9]|3[1-8]|4[1-8]|5[1-6]|7[1-7]|9[1-8])\d{5,8}$/', $digits) === 1) {
            return true;
        }

        // Cek fallback teks jika diawali 021 atau (021)
        return (bool) preg_match('/^(?:\+?62|0)?21[\s\)\-\.]*\d{6,8}$/', trim((string) $value));
    }

    /**
     * Cek apakah nomor mendukung panggilan/chat WhatsApp (hanya nomor HP).
     */
    public static function isWhatsappSupported(?string $value): bool
    {
        return self::isMobile($value);
    }

    /**
     * Format nomor untuk tampilan visual tabel & profil:
     * - Nomor kantor: (021) xxx-xxxx
     * - Nomor HP: 628...
     * - Nomor lainnya: dibersihkan dari kurung cacat / spasi acak secara rapi tanpa kata negatif.
     */
    public static function formatDisplay(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '-';
        }

        // Jika HP valid
        if (self::isMobile($raw)) {
            return self::normalize($raw);
        }

        $digits = preg_replace('/\D/', '', $raw) ?? '';
        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        // Format telepon kantor Jakarta (021)
        if (str_starts_with($digits, '021') && strlen($digits) >= 9 && strlen($digits) <= 12) {
            $local = substr($digits, 3);
            if (strlen($local) === 7) {
                return '(021) '.substr($local, 0, 3).'-'.substr($local, 3);
            } elseif (strlen($local) === 8) {
                return '(021) '.substr($local, 0, 4).'-'.substr($local, 4);
            }

            return '(021) '.$local;
        }

        // Format telepon kantor kode area 3 digit lainnya (022, 031, 024, dll.)
        if (preg_match('/^0(2[2-9]|3[1-8]|4[1-8]|5[1-6]|7[1-7]|9[1-8])(\d{6,8})$/', $digits, $matches)) {
            $area = $matches[1];
            $local = $matches[2];
            $half = (int) ceil(strlen($local) / 2);

            return "(0{$area}) ".substr($local, 0, $half).'-'.substr($local, $half);
        }

        // Sanitasi nomor non-standar: perbaiki kurung cacat seperti "021) ..." -> "(021) ..."
        $cleaned = $raw;
        if (str_contains($cleaned, ')') && ! str_contains($cleaned, '(')) {
            $cleaned = preg_replace('/^(\+?\d+)\)\s*/', '($1) ', $cleaned) ?? $cleaned;
        }

        $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/-+/', '-', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    /**
     * Sanitasi nomor saat import / input data.
     */
    public static function cleanRawNumber(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (self::isMobile($raw)) {
            return self::normalize($raw);
        }

        // Jika nomor telepon kantor yang dikenali, simpan versi format rapi
        if (self::isLandline($raw)) {
            return self::formatDisplay($raw);
        }

        // Sanitasi kurung cacat dan spasi berlebih
        $cleaned = $raw;
        if (str_contains($cleaned, ')') && ! str_contains($cleaned, '(')) {
            $cleaned = preg_replace('/^(\+?\d+)\)\s*/', '($1) ', $cleaned) ?? $cleaned;
        }

        $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/-+/', '-', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    /**
     * Format URL WhatsApp resmi (https://wa.me/628...).
     * Mengembalikan '#' jika nomor bukan nomor HP atau kosong.
     */
    public static function whatsappUrl(?string $value, ?string $message = null): string
    {
        if (! self::isWhatsappSupported($value)) {
            return '#';
        }

        $phone = self::normalize($value);

        if ($phone === '') {
            return '#';
        }

        $url = 'https://wa.me/'.$phone;

        if (filled($message)) {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }

    /**
     * Menghasilkan elemen SVG ikon resmi WhatsApp yang tajam dan responsif.
     */
    public static function whatsappIconHtml(string $class = 'w-5 h-5'): \Illuminate\Support\HtmlString
    {
        return new \Illuminate\Support\HtmlString(
            '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' .
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.83 14.09c-.24.68-1.2 1.25-1.68 1.3-.47.05-1.07.07-3.44-.86-2.85-1.12-4.66-4.04-4.8-4.23-.14-.2-1.17-1.56-1.17-2.98 0-1.42.74-2.12 1.03-2.41.28-.29.62-.36.83-.36.21 0 .42.002.6.012.19.01.45.07.69.65.25.6.86 2.1.94 2.25.08.15.13.34.03.54-.1.2-.15.33-.3.5-.15.18-.32.4-.45.54-.15.15-.31.32-.13.62.18.3 1.03 1.7 2.32 2.85 1.43 1.28 2.64 1.67 3.01 1.86.37.19.59.16.8-.09.22-.25.93-1.08 1.18-1.45.25-.37.49-.31.83-.19.34.12 2.16 1.02 2.53 1.21.37.19.62.28.71.43.09.16.09.9-.15 1.58z"/>' .
            '</svg>'
        );
    }
}
