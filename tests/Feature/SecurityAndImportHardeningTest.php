<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SecuritySanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndImportHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'     => 'admin',
            'username' => 'admin_sec',
        ]);

        $this->staffUser = User::factory()->create([
            'role'     => 'staff',
            'username' => 'staff_sec',
        ]);
    }

    /**
     * Test formula injection sanitization on cell values starting with =, +, -, @.
     */
    public function test_formula_injection_cells_are_sanitized(): void
    {
        $maliciousCell1 = "=CMD|' /C calc'!A0";
        $maliciousCell2 = "+SUM(A1:A10)";
        $maliciousCell3 = "@SUM(A1:A10)";
        $normalNumber   = "-125.50";

        $sanitized1 = SecuritySanitizer::sanitizeCell($maliciousCell1);
        $sanitized2 = SecuritySanitizer::sanitizeCell($maliciousCell2);
        $sanitized3 = SecuritySanitizer::sanitizeCell($maliciousCell3);
        $sanitizedNorm = SecuritySanitizer::sanitizeCell($normalNumber);

        $this::assertEquals("'=CMD|' /C calc'!A0", $sanitized1);
        $this::assertEquals("'+SUM(A1:A10)", $sanitized2);
        $this::assertEquals("'@SUM(A1:A10)", $sanitized3);
        $this::assertEquals("-125.50", $sanitizedNorm);
    }

    /**
     * Test HTML / XSS string sanitization.
     */
    public function test_html_xss_inputs_are_sanitized(): void
    {
        $dirtyInput = "<script>alert('xss')</script><b>Material</b>";
        $cleanOutput = SecuritySanitizer::sanitizeString($dirtyInput);

        $this::assertStringNotContainsString('<script>', $cleanOutput);
        $this::assertStringNotContainsString('</script>', $cleanOutput);
        $this::assertEquals('alert(&#039;xss&#039;)Material', $cleanOutput);
    }

    /**
     * Test unauthenticated access to protected routes redirects to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('purchasing.history'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test non-admin staff user cannot access user management.
     */
    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('users.index'));
        $response->assertStatus(403);
    }

    /**
     * Test file upload validation rejects invalid extensions (e.g. .php, .exe).
     */
    public function test_excel_import_rejects_disallowed_file_types(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->actingAs($this->staffUser)->post(route('purchasing.outstanding.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
    }
}
