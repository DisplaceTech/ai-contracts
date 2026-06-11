<?php

declare(strict_types=1);

namespace Displace\AI\Contracts;

/**
 * Speech-to-text transcription.
 *
 * Takes a path to an audio file and returns the transcribed text plus
 * timestamped segments. Accepted audio formats are an implementation
 * concern and MUST be documented by the implementation (a local
 * whisper.cpp driver may accept only 16kHz mono WAV; a hosted API may
 * accept anything ffmpeg can read).
 */
interface Transcriber
{
    /**
     * Transcribe the audio file at `$audioPath`.
     *
     * `text` is the full transcript. `segments` are time-ordered spans
     * with `start`/`end` offsets in seconds; their concatenated `text`
     * is equivalent to the full transcript modulo whitespace.
     *
     * Recognised option keys — implementations MUST silently ignore keys
     * they do not understand:
     *
     * - `language` (string) ISO 639-1 hint, e.g. `'en'`; autodetect when
     *                       absent, where supported.
     *
     * @param array<string, mixed> $options
     *
     * @return array{
     *     text: string,
     *     segments: list<array{start: float, end: float, text: string}>,
     * }
     */
    public function transcribe(string $audioPath, array $options = []): array;
}
