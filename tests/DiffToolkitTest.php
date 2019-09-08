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
class DiffToolkitTest extends \PHPUnit\Framework\TestCase {
    /**
     * @var DiffToolkit
     */
    protected $dt;

    protected  function setUp() : void {
        mb_internal_encoding('UTF-8');

        $this->dt = new DiffToolkit();
    }

    public function testCommonPrefixNull() : void {
        // Null case.
        $this->assertEquals(0, $this->dt->commonPrefix("abc", "xyz"));
    }

    public function testCommonPrefixNotNull() : void {
        // Non-null case.
        $this->assertEquals(4, $this->dt->commonPrefix("1234abcdef", "1234xyz"));
    }

    public function testCommonPrefixWhole() : void {
        // Detect any common prefix.
        // Whole case.
        $this->assertEquals(4, $this->dt->commonPrefix("1234", "1234xyz"));
    }

    public function testCommonSuffixNull() : void {
        // Detect any common suffix.
        // Null case.
        $this->assertEquals(0, $this->dt->commonSuffix("abc", "xyz"));
    }

    public function testCommonSuffixNotNull() : void {
        // Non-null case.
        $this->assertEquals(4, $this->dt->commonSuffix("abcdef1234", "xyz1234"));
    }

    public function testCommonSuffixWhole() : void {
        // Detect any common suffix.
        // Whole case.
        $this->assertEquals(4, $this->dt->commonSuffix("1234", "xyz1234"));
    }

    public function testCommonOverlapNull() : void {
        # Null case.
        $this->assertEquals(0, $this->dt->commonOverlap("", "abcd"));
    }

    public function testCommonOverlapWhole() : void {
        // Whole case.
        $this->assertEquals(3, $this->dt->commonOverlap("abc", "abcd"));
    }

    public function testCommonOverlapNoOverlap() : void {
        // No overlap.
        $this->assertEquals(0, $this->dt->commonOverlap("123456", "abcd"));
    }

    public function testCommonOverlap() : void {
        // Overlap.
        $this->assertEquals(3, $this->dt->commonOverlap("123456xxx", "xxxabcd"));
    }

    public function testCommonOverlapUnicode() : void {
        // Unicode.
        // Some overly clever languages (C#) may treat ligatures as equal to their
        // component letters.  E.g. U+FB01 == 'fi'
        $this->assertEquals(0, $this->dt->commonOverlap("fi", json_decode('"\ufb01"')));
    }

    public function testHalfMatchNoMatch() : void {
        // No match.
        $this->assertNull($this->dt->halfMatch("1234567890", "abcdef"));
        $this->assertNull($this->dt->halfMatch("12345", "23"));
    }

    public function testHalfMatchSingleMatch() : void {
        // Single Match.
        $this->assertEquals(array("12", "90", "a", "z", "345678"), $this->dt->halfMatch("1234567890", "a345678z"));
        $this->assertEquals(array("a", "z", "12", "90", "345678"), $this->dt->halfMatch("a345678z", "1234567890"));
        $this->assertEquals(array("abc", "z", "1234", "0", "56789"), $this->dt->halfMatch("abc56789z", "1234567890"));
        $this->assertEquals(array("a", "xyz", "1", "7890", "23456"), $this->dt->halfMatch("a23456xyz", "1234567890"));
    }

    public function testHalfMatchMultipleMatches() : void {
        // Multiple Matches.
        $this->assertEquals(array("12123", "123121", "a", "z", "1234123451234"), $this->dt->halfMatch("121231234123451234123121", "a1234123451234z"));
        $this->assertEquals(array("", "-=-=-=-=-=", "x", "", "x-=-=-=-=-=-=-="), $this->dt->halfMatch("x-=-=-=-=-=-=-=-=-=-=-=-=", "xx-=-=-=-=-=-=-="));
        $this->assertEquals(array("-=-=-=-=-=", "", "", "y", "-=-=-=-=-=-=-=y"), $this->dt->halfMatch("-=-=-=-=-=-=-=-=-=-=-=-=y", "-=-=-=-=-=-=-=yy"));
    }

    public function testHalfMatchNonOptimalHalfmatch() : void {
        // Non-optimal halfmatch.
        // Optimal diff would be -q+x=H-i+e=lloHe+Hu=llo-Hew+y not -qHillo+x=HelloHe-w+Hulloy
        $this->assertEquals(array("qHillo", "w", "x", "Hulloy", "HelloHe"), $this->dt->halfMatch("qHilloHelloHew", "xHelloHeHulloy"));
    }

    public function testLinesToChars() : void {
        // TODO throw exception, if charset is one-byte
        mb_internal_encoding('UTF-8');

        // Convert lines down to characters.
        $this->assertEquals(
            array("\x01\x02\x01", "\x02\x01\x02", array("", "alpha\n", "beta\n")),
            $this->dt->linesToChars("alpha\nbeta\nalpha\n", "beta\nalpha\nbeta\n")
        );
        $this->assertEquals(
            array("", "\x01\x02\x03\x03", array("", "alpha\r\n", "beta\r\n", "\r\n")),
            $this->dt->linesToChars("", "alpha\r\nbeta\r\n\r\n\r\n")
        );
        $this->assertEquals(
            array("\x01", "\x02", array("", "a", "b")),
            $this->dt->linesToChars("a", "b")
        );

        // More than 256 to reveal any 8-bit limitations.
        $n = 300;
        $lineList = array();
        $charList = array();

        for ($x = 1; $x <= $n; $x++) {
            $lineList[] = $x . "\n";
            $charList[] = mb_chr($x);
        }
        $this->assertCount($n, $lineList);

        $lines = implode('', $lineList);
        $chars = implode('', $charList);
        $this->assertEquals($n, mb_strlen($chars));

        array_unshift($lineList, "");
        $this->assertEquals(
            array($chars, "", $lineList),
            $this->dt->linesToChars($lines, "")
        );
    }

    public function testCharsToLines() : void {
        // Convert chars up to lines.
        $diffs = array(
            array(Diff::EQUAL,  "\x01\x02\x01"),
            array(Diff::INSERT, "\x02\x01\x02")
        );
        $this->dt->charsToLines($diffs, array("", "alpha\n", "beta\n"));
        $this->assertEquals(array(
            array(Diff::EQUAL,  "alpha\nbeta\nalpha\n"),
            array(Diff::INSERT, "beta\nalpha\nbeta\n")
        ), $diffs);

        // More than 256 to reveal any 8-bit limitations.
        $n = 300;
        $lineList = array();
        $charList = array();

        for ($x = 1; $x <= $n; $x++) {
            $lineList[] = "$x\n";
            $charList[] = mb_chr($x);
        }
        $this->assertCount($n, $lineList);

        $lines = implode('', $lineList);
        $chars = implode('', $charList);
        $this->assertEquals($n, mb_strlen($chars));

        array_unshift($lineList, "");
        $diffs = array(
            array(Diff::DELETE, $chars)
        );
        $this->dt->charsToLines($diffs, $lineList);
        $this->assertEquals(array(
            array(Diff::DELETE, $lines),
        ), $diffs);
    }

}
