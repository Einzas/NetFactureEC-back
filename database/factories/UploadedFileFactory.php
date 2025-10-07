<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use App\Models\SubUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UploadedFile>
 */
class UploadedFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = $this->faker->randomElement(['pdf', 'xml', 'p12']);
        $fileName = $this->faker->uuid() . '.' . $extension;
        
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'sub_user_id' => null,
            'original_name' => $fileName,
            'stored_name' => $fileName,
            'file_path' => 'uploads/' . $fileName,
            'mime_type' => $this->getMimeType($extension),
            'extension' => $extension,
            'size' => $this->faker->numberBetween(1000, 5000000),
            'file_type' => $extension,
            'expires_at' => null,
            'session_id' => null,
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Get MIME type for file extension.
     */
    protected function getMimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'p12', 'pfx' => 'application/x-pkcs12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            default => 'application/octet-stream',
        };
    }

    /**
     * Indicate that the file is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the file is a session file.
     */
    public function sessionFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => Str::uuid(),
            'expires_at' => now()->addHours(2),
        ]);
    }

    /**
     * Indicate that the file is a P12 certificate.
     */
    public function p12Certificate(): static
    {
        $uuid = Str::uuid();
        
        return $this->state(fn (array $attributes) => [
            'original_name' => 'certificate.p12',
            'stored_name' => $uuid . '.p12',
            'file_path' => "uploads/user_{$attributes['user_id']}/p12/{$uuid}.p12",
            'mime_type' => 'application/x-pkcs12',
            'extension' => 'p12',
            'file_type' => 'p12',
            'session_id' => Str::uuid(),
            'expires_at' => now()->addHours(2),
        ]);
    }
}
