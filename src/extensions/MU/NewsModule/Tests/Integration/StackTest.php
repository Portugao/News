<?php

/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 *
 * @see https://homepages-mit-zikula.de
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

declare(strict_types=1);

namespace MU\NewsModule\Tests\Integration;

use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    public function testEmpty(): array
    {
        $stack = [];
        self::assertEmpty($stack);

        return $stack;
    }

    /**
     * @depends testEmpty
     */
    public function testPush(array $stack): array
    {
        $stack[] = 'foo';
        self::assertEquals('foo', $stack[count($stack) - 1]);
        self::assertNotEmpty($stack);

        return $stack;
    }

    /**
     * @depends testPush
     */
    public function testPop(array $stack): void
    {
        self::assertEquals('foo', array_pop($stack));
        self::assertEmpty($stack);
    }
}
