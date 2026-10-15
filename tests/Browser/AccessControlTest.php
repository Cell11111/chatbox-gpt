<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class AccessControlTest extends DuskTestCase
{
    #[Test]
    public function unauthenticated_access_to_view_project_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->driver->executeScript("window.location.href = 'about:blank';");
            $browser->pause(500);
            $browser->visit('/view-project')
                ->pause(1500)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function unauthenticated_access_to_submit_requirements_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->driver->executeScript("window.location.href = 'about:blank';");
            $browser->pause(500);
            $browser->visit('/submit-requirements')
                ->pause(1500)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function unauthenticated_access_to_pending_updates_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->driver->executeScript("window.location.href = 'about:blank';");
            $browser->pause(500);
            $browser->visit('/pending-updates')
                ->pause(1500)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function unauthenticated_access_to_conflicts_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->driver->executeScript("window.location.href = 'about:blank';");
            $browser->pause(500);
            $browser->visit('/view-conflict')
                ->pause(1500)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function unauthenticated_access_to_defects_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->driver->executeScript("window.location.href = 'about:blank';");
            $browser->pause(500);
            $browser->visit('/review-defects')
                ->pause(1500)
                ->assertPathIs('/login');
        });
    }

    #[Test]
    public function authenticated_user_can_access_protected_routes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(1000)
                ->type('[dusk="email-input"]', 'engineer@example.com')
                ->type('[dusk="password-input"]', '1234')
                ->click('[dusk="login-button"]')
                ->pause(2000)
                ->assertPathIs('/')
                ->visit('/view-project')
                ->pause(1000);
        });
    }
}


