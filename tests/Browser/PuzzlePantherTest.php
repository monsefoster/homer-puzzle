<?php

declare(strict_types=1);

namespace App\Tests\Browser;

final class PuzzlePantherTest extends BrowserTestCase
{
    public function test_it_loads_the_initial_puzzle_state(): void
    {
        self::ensureBrowserRuntime();

        $client = self::createPantherClient();
        $client->request('GET', '/');
        $client->waitFor('#puzzle-app');

        self::assertSelectorTextContains('h1', 'PROYECTO 1');
        self::assertSelectorTextContains('.state-code', '2-0-1-3-2-1-3');
    }

    public function test_it_updates_the_board_via_the_enhanced_joint_mode_flow(): void
    {
        self::ensureBrowserRuntime();

        $client = self::createPantherClient();
        $client->request('GET', '/');
        $client->waitFor('#puzzle-app');

        $client->getCrawler()->filter('form[action="/move/2"] .dial-button')->click();

        $client->waitForAttributeToContain('#puzzle-app', 'class', 'is-fresh', 5, 25);
        $client->waitForElementToContain('.state-code', '2-0-2-0-3-2-0', 5, 25);

        self::assertSelectorTextContains('.state-code', '2-0-2-0-3-2-0');
    }

    public function test_it_switches_modes_and_uses_the_individual_rule_set(): void
    {
        self::ensureBrowserRuntime();

        $client = self::createPantherClient();
        $client->request('GET', '/');
        $client->waitFor('#puzzle-app');

        $client->getCrawler()->filterXPath('//form[@action="/mode"][.//input[@value="individual"]]//button')->click();
        $client->waitForAttributeToContain('#puzzle-app', 'class', 'is-fresh', 5, 25);
        $client->waitForElementToContain('.summary .card:nth-child(1) strong', 'Individual', 5, 25);

        $client->getCrawler()->filter('form[action="/move/2"] .dial-button')->click();
        $client->waitForAttributeToContain('#puzzle-app', 'class', 'is-fresh', 5, 25);
        $client->waitForElementToContain('.state-code', '2-0-2-3-2-1-3', 5, 25);

        self::assertSelectorTextContains('.state-code', '2-0-2-3-2-1-3');
    }

    public function test_it_can_reset_the_board_after_a_move(): void
    {
        self::ensureBrowserRuntime();

        $client = self::createPantherClient();
        $client->request('GET', '/');
        $client->waitFor('#puzzle-app');

        $client->getCrawler()->filter('form[action="/move/2"] .dial-button')->click();
        $client->waitForElementToContain('.state-code', '2-0-2-0-3-2-0', 5, 25);

        $client->getCrawler()->filter('form[action="/reset"] .reset-button')->click();
        $client->waitForAttributeToContain('#puzzle-app', 'class', 'is-fresh', 5, 25);
        $client->waitForElementToContain('.state-code', '2-0-1-3-2-1-3', 5, 25);

        self::assertSelectorTextContains('.state-code', '2-0-1-3-2-1-3');
    }
}
