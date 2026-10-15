<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class LoginLogoutTest extends DuskTestCase
{
    #[Test]
    public function manager_can_login_access_protected_and_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->screenshot('01-login-page')
                ->assertSee('Login')
                ->type('@email-input', 'manager@example.com')
                ->screenshot('02-after-typing-email')
                ->type('@password-input', '1234')
                ->click('@login-button')
                ->pause(1500)
                ->screenshot('03-after-login')
                ->assertPathIs('/')
                ->visit('/view-project')
                ->assertSee('Project Overview')
                ->clickLink('Logout')
                ->pause(1000)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function engineer_can_login_access_protected_and_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Login')
                ->type('@email-input', 'engineer@example.com')
                ->type('@password-input', '1234')
                ->click('@login-button')
                ->pause(1500)
                ->assertPathIs('/')
                ->visit('/submit-requirements')
                ->assertSee('Submit Requirement')
                ->clickLink('Logout')
                ->pause(1000)
                ->assertPathIs('/login');
        });
    }
}