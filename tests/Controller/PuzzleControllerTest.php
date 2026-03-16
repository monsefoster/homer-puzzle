<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PuzzleControllerTest extends WebTestCase
{
    public function test_the_home_page_renders_the_puzzle(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Proyecto 1');
        self::assertSelectorTextContains('.state-code', '2-0-1-3-2-1-3');
    }

    public function test_joint_mode_rotates_the_suffix(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $client->request('POST', '/move/2');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.state-code', '2-0-2-0-3-2-0');
    }

    public function test_individual_mode_rotates_only_the_selected_dial(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $client->request('POST', '/mode', ['mode' => 'individual']);
        $client->followRedirect();
        $client->request('POST', '/move/2');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.state-code', '2-0-2-3-2-1-3');
    }

    public function test_ajax_move_returns_the_partial_app_markup(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $client->request('POST', '/move/2', server: ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('<html', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('2-0-2-0-3-2-0', (string) $client->getResponse()->getContent());
    }
}
