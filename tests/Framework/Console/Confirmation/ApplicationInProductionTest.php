<?php

declare(strict_types=1);

namespace Framework\Console\Confirmation;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Console\Confirmation\ApplicationInProduction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApplicationInProductionTest extends TestCase
{
    #[DataProvider('notProductionEnvs')]
    public function testNotProductionEnvironmentShouldBeConfirmed(AppEnvironment $env): void
    {
        $confirmation = new ApplicationInProduction(
            $env,
            m::mock(InputInterface::class),
            m::mock(OutputInterface::class)
        );

        $this->assertTrue($confirmation->confirmToProceed());
    }

    public function testProductionEnvWithForceOptionShouldBeConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = m::mock(InputInterface::class),
            m::mock(OutputInterface::class)
        );

        $input->shouldReceive('hasOption')->once()->andReturnTrue();
        $input->shouldReceive('getOption')->once()->andReturnTrue();

        $this->assertTrue($confirmation->confirmToProceed());
    }

    public function testProductionEnvShouldBeAskAboutConfirmationAndConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = m::mock(InputInterface::class),
            $output = m::mock(OutputInterface::class)
        );

        $input->shouldReceive('hasOption')->once()->andReturnFalse();

        $output->shouldReceive('writeln');
        $output->shouldReceive('write');
        $output->shouldReceive('newLine');

        $output->shouldReceive('confirm')->once()->with('Do you really wish to run command?', false)->andReturnTrue();

        $this->assertTrue($confirmation->confirmToProceed());
    }

    public function testProductionEnvShouldBeAskAboutConfirmationAndNotConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = m::mock(InputInterface::class),
            $output = m::mock(OutputInterface::class)
        );

        $input->shouldReceive('hasOption')->once()->andReturnFalse();

        $output->shouldReceive('writeln');
        $output->shouldReceive('write');
        $output->shouldReceive('newLine');

        $output->shouldReceive('confirm')->once()->with('Do you really wish to run command?', false)->andReturnFalse();

        $this->assertFalse($confirmation->confirmToProceed());
    }

    public static function notProductionEnvs(): \Traversable
    {
        yield 'Local' => [AppEnvironment::Local];
        yield 'Testing' => [AppEnvironment::Testing];
        yield 'Stage' => [AppEnvironment::Stage];
    }
}
