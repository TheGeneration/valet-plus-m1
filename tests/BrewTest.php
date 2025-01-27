<?php

use Valet\Brew;
use Valet\Filesystem;
use Valet\CommandLine;
use Illuminate\Container\Container;

class BrewTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['SUDO_USER'] = user();

        Container::setInstance(new Container);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function test_brew_can_be_resolved_from_container()
    {
        $this->assertInstanceOf(Brew::class, resolve(Brew::class));
    }


    public function test_installed_returns_true_when_given_formula_is_installed()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep php71')->andReturn('php71');
        swap(CommandLine::class, $cli);
        $this->assertTrue(resolve(Brew::class)->installed('php71'));

        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep php71')->andReturn('php71-mcrypt
php71');
        swap(CommandLine::class, $cli);
        $this->assertTrue(resolve(Brew::class)->installed('php71'));
    }


    public function test_installed_returns_false_when_given_formula_is_not_installed()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep php71')->andReturn('');
        swap(CommandLine::class, $cli);
        $this->assertFalse(resolve(Brew::class)->installed('php71'));

        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep php71')->andReturn('php71-mcrypt');
        swap(CommandLine::class, $cli);
        $this->assertFalse(resolve(Brew::class)->installed('php71'));

        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep php71')->andReturn('php71-mcrypt
php71-something-else
php7');
        swap(CommandLine::class, $cli);
        $this->assertFalse(resolve(Brew::class)->installed('php71'));
    }


    public function test_has_installed_php_indicates_if_php_is_installed_via_brew()
    {
        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(true);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(false);
        $this->assertTrue($brew->hasInstalledPhp());

        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(true);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(false);
        $this->assertTrue($brew->hasInstalledPhp());

        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(true);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(false);
        $this->assertTrue($brew->hasInstalledPhp());

        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(true);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(false);
        $this->assertTrue($brew->hasInstalledPhp());

        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(true);
        $this->assertTrue($brew->hasInstalledPhp());

        $brew = Mockery::mock(Brew::class.'[installed]', [new CommandLine, new Filesystem]);
        $brew->shouldReceive('installed')->with('php')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.2')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.1')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@7.0')->andReturn(false);
        $brew->shouldReceive('installed')->with('php@5.6')->andReturn(false);
        $this->assertFalse($brew->hasInstalledPhp());
    }


    public function test_tap_taps_the_given_homebrew_repository()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('passthru')->once()->with('sudo -u '.user().' brew tap php71');
        $cli->shouldReceive('passthru')->once()->with('sudo -u '.user().' brew tap php70');
        $cli->shouldReceive('passthru')->once()->with('sudo -u '.user().' brew tap php56');
        swap(CommandLine::class, $cli);
        resolve(Brew::class)->tap('php71', 'php70', 'php56');
    }


    public function test_restart_restarts_the_service_using_homebrew_services()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep dnsmasq')->andReturn('dnsmasq');
        $cli->shouldReceive('quietly')->once()->with('sudo brew services stop dnsmasq');
        $cli->shouldReceive('quietly')->once()->with('sudo brew services start dnsmasq');
        swap(CommandLine::class, $cli);
        resolve(Brew::class)->restartService('dnsmasq');
    }


    public function test_stop_stops_the_service_using_homebrew_services()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew list | grep dnsmasq')->andReturn('dnsmasq');
        $cli->shouldReceive('quietly')->once()->with('sudo brew services stop dnsmasq');
        swap(CommandLine::class, $cli);
        resolve(Brew::class)->stopService('dnsmasq');
    }


    public function test_linked_php_returns_linked_php_formula_name()
    {
        $files = Mockery::mock(Filesystem::class);
        $files->shouldReceive('isLink')->once()->with('/opt/homebrew/bin/php')->andReturn(true);
        $files->shouldReceive('readLink')->once()->with('/opt/homebrew/bin/php')->andReturn('/test/path/php@7.1/test');
        swap(Filesystem::class, $files);
        $this->assertSame('php@7.1', resolve(Brew::class)->linkedPhp(true));

        $files = Mockery::mock(Filesystem::class);
        $files->shouldReceive('isLink')->once()->with('/opt/homebrew/bin/php')->andReturn(true);
        $files->shouldReceive('readLink')->once()->with('/opt/homebrew/bin/php')->andReturn('/test/path/php@5.6/test');
        swap(Filesystem::class, $files);
        $this->assertSame('php@5.6', resolve(Brew::class)->linkedPhp(true));
    }


    /**
     * @expectedException DomainException
     */
    public function test_linked_php_throws_exception_if_no_php_link()
    {
        $files = Mockery::mock(Filesystem::class);
        $files->shouldReceive('isLink')->once()->with('/opt/homebrew/bin/php')->andReturn(false);
        swap(Filesystem::class, $files);
        resolve(Brew::class)->linkedPhp();
    }


    /**
     * @expectedException DomainException
     */
    public function test_linked_php_throws_exception_if_unsupported_php_version_is_linked()
    {
        $files = Mockery::mock(Filesystem::class);
        $files->shouldReceive('isLink')->once()->with('/opt/homebrew/bin/php')->andReturn(true);
        $files->shouldReceive('readLink')->once()->with('/opt/homebrew/bin/php')->andReturn('/test/path/php42/test');
        swap(Filesystem::class, $files);
        resolve(Brew::class)->linkedPhp();
    }


    public function test_install_or_fail_will_install_brew_formulas()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew install dnsmasq', Mockery::type('Closure'));
        swap(CommandLine::class, $cli);
        resolve(Brew::class)->installOrFail('dnsmasq');
    }


    public function test_install_or_fail_can_install_taps()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->once()->with('brew install dnsmasq', Mockery::type('Closure'));
        swap(CommandLine::class, $cli);
        $brew = Mockery::mock(Brew::class.'[tap]', [$cli, new Filesystem]);
        $brew->shouldReceive('tap')->once()->with(['test/tap']);
        $brew->installOrFail('dnsmasq', [], ['test/tap']);
    }


    /**
     * @expectedException DomainException
     */
    public function test_install_or_fail_throws_exception_on_failure()
    {
        $cli = Mockery::mock(CommandLine::class);
        $cli->shouldReceive('runAsUser')->andReturnUsing(function ($command, $onError) {
            $onError(1, 'test error ouput');
        });
        swap(CommandLine::class, $cli);
        resolve(Brew::class)->installOrFail('dnsmasq');
    }
}
