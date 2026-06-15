<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_update_smtp_and_notification_settings()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'primary_color' => '#123456',
        ]);
        
        $organization->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->patch('/organization', [
            'name' => 'Updated Org',
            'slug' => 'updated-org',
            'primary_color' => '#654321',
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => 2525,
            'smtp_username' => 'testuser',
            'smtp_password' => 'testpass',
            'smtp_from_email' => 'hello@updated-org.com',
            'smtp_from_name' => 'Updated Org Team',
            'notify_daily_sales' => 'on',
            'notify_new_order' => 'on',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $organization->refresh();

        $this->assertEquals('smtp.mailtrap.io', $organization->smtp_host);
        $this->assertEquals(2525, $organization->smtp_port);
        $this->assertEquals('testuser', $organization->smtp_username);
        $this->assertEquals('testpass', $organization->smtp_password);
        $this->assertEquals('hello@updated-org.com', $organization->smtp_from_email);
        $this->assertEquals('Updated Org Team', $organization->smtp_from_name);
        
        $this->assertIsArray($organization->settings);
        $this->assertTrue($organization->settings['notify_daily_sales']);
        $this->assertTrue($organization->settings['notify_new_order']);
    }
}
