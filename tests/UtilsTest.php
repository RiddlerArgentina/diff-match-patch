<?php declare(strict_types=1);
/*
 * DiffMatchPatch is a port of the google-diff-match-patch
 * (http://code.google.com/p/google-diff-match-patch/) lib to PHP.
 *
 * (c) 2006 Google Inc.
 * (c) 2013 Daniil Skrobov <yetanotherape@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace DiffMatchPatch;

/**
 * @package DiffMatchPatch
 * @author Neil Fraser <fraser@google.com>
 * @author Daniil Skrobov <yetanotherape@gmail.com>
 */
class UtilsTest extends \PHPUnit\Framework\TestCase
{
    protected  function setUp() : void {
        mb_internal_encoding('UTF-8');
    }

    public function testUnicodeChr() : void {
        $this->assertEquals(mb_chr(97), Utils::unicodeChr(97));
        $this->assertEquals(mb_chr(255), Utils::unicodeChr(255));
        $this->assertEquals(mb_chr(256), Utils::unicodeChr(256));
        $this->assertEquals(mb_chr(260), Utils::unicodeChr(260));
//        $this->assertEquals(mb_chr(65536), Utils::unicodeChr(65536));
//        $this->assertEquals(mb_chr(128570), Utils::unicodeChr(128570));
    }

    public function testUnicodeOrd() : void {
        $this->assertEquals(mb_ord('a'), Utils::unicodeOrd('a'));
        $this->assertEquals(mb_ord('ÿ'), Utils::unicodeOrd('ÿ'));
        $this->assertEquals(mb_ord('Ā'), Utils::unicodeOrd('Ā'));
        $this->assertEquals(mb_ord('Ą'), Utils::unicodeOrd('Ą'));
//        $this->assertEquals(mb_ord('𐀀'), Utils::unicodeOrd('𐀀'));
//        $this->assertEquals(mb_ord('😺'), Utils::unicodeOrd('😺'));
    }


}
