<?php
namespace Consolidation\OutputFormatters;

use Consolidation\TestUtils\AssociativeListWithCsvCells;
use Consolidation\TestUtils\RowsOfFieldsWithAlternatives;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\AssociativeList;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class FormattersTests extends \PHPUnit_Framework_TestCase
{
    protected $formatterManager;

    function setup() {
        $this->formatterManager = new FormatterManager();
    }

    function assertFormattedOutputMatches($expected, $format, $data, FormatterOptions $options = null, $userOptions = []) {
        if (!$options) {
            $options = new FormatterOptions();
        }
        $options->setOptions($userOptions);
        $output = new BufferedOutput();
        $this->formatterManager->write($output, $format, $data, $options);
        $actual = preg_replace('#[ \t]*$#sm', '', $output->fetch());
        $this->assertEquals(rtrim($expected), rtrim($actual));
    }

    function testSimpleYaml()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = <<<EOT
one: a
two: b
three: c
EOT;

        $this->assertFormattedOutputMatches($expected, 'yaml', $data);
    }

    function testNestedYaml()
    {
        $data = [
            'one' => [
                'i' => ['a', 'b', 'c'],
            ],
            'two' => [
                'ii' => ['q', 'r', 's'],
            ],
            'three' => [
                'iii' => ['t', 'u', 'v'],
            ],
        ];

        $expected = <<<EOT
one:
  i:
    - a
    - b
    - c
two:
  ii:
    - q
    - r
    - s
three:
  iii:
    - t
    - u
    - v
EOT;

        $this->assertFormattedOutputMatches($expected, 'yaml', $data);
    }

    function testSimpleJson()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = <<<EOT
{
    "one": "a",
    "two": "b",
    "three": "c"
}
EOT;

        $this->assertFormattedOutputMatches($expected, 'json', $data);
    }

    function testSerializeFormat()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = 'a:3:{s:3:"one";s:1:"a";s:3:"two";s:1:"b";s:5:"three";s:1:"c";}';

        $this->assertFormattedOutputMatches($expected, 'php', $data);
    }

    function testNestedJson()
    {
        $data = [
            'one' => [
                'i' => ['a', 'b', 'c'],
            ],
            'two' => [
                'ii' => ['q', 'r', 's'],
            ],
            'three' => [
                'iii' => ['t', 'u', 'v'],
            ],
        ];

        $expected = <<<EOT
{
    "one": {
        "i": [
            "a",
            "b",
            "c"
        ]
    },
    "two": {
        "ii": [
            "q",
            "r",
            "s"
        ]
    },
    "three": {
        "iii": [
            "t",
            "u",
            "v"
        ]
    }
}
EOT;

        $this->assertFormattedOutputMatches($expected, 'json', $data);
    }

    function testSimplePrintR()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = <<<EOT
Array
(
    [one] => a
    [two] => b
    [three] => c
)
EOT;

        $this->assertFormattedOutputMatches($expected, 'print-r', $data);
    }

    function testNestedPrintR()
    {
        $data = [
            'one' => [
                'i' => ['a', 'b', 'c'],
            ],
            'two' => [
                'ii' => ['q', 'r', 's'],
            ],
            'three' => [
                'iii' => ['t', 'u', 'v'],
            ],
        ];

        $expected = <<<EOT
Array
(
    [one] => Array
        (
            [i] => Array
                (
                    [0] => a
                    [1] => b
                    [2] => c
                )

        )

    [two] => Array
        (
            [ii] => Array
                (
                    [0] => q
                    [1] => r
                    [2] => s
                )

        )

    [three] => Array
        (
            [iii] => Array
                (
                    [0] => t
                    [1] => u
                    [2] => v
                )

        )

)
EOT;

        $this->assertFormattedOutputMatches($expected, 'print-r', $data);
    }

    function testSimpleVarExport()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = <<<EOT
array (
  'one' => 'a',
  'two' => 'b',
  'three' => 'c',
)
EOT;

        $this->assertFormattedOutputMatches($expected, 'var_export', $data);
    }

    function testNestedVarExport()
    {
        $data = [
            'one' => [
                'i' => ['a', 'b', 'c'],
            ],
            'two' => [
                'ii' => ['q', 'r', 's'],
            ],
            'three' => [
                'iii' => ['t', 'u', 'v'],
            ],
        ];

        $expected = <<<EOT
array (
  'one' =>
  array (
    'i' =>
    array (
      0 => 'a',
      1 => 'b',
      2 => 'c',
    ),
  ),
  'two' =>
  array (
    'ii' =>
    array (
      0 => 'q',
      1 => 'r',
      2 => 's',
    ),
  ),
  'three' =>
  array (
    'iii' =>
    array (
      0 => 't',
      1 => 'u',
      2 => 'v',
    ),
  ),
)
EOT;

        $this->assertFormattedOutputMatches($expected, 'var_export', $data);
    }

    function testList()
    {
        $data = [
            'one' => 'a',
            'two' => 'b',
            'three' => 'c',
        ];

        $expected = <<<EOT
a
b
c
EOT;

        $this->assertFormattedOutputMatches($expected, 'list', $data);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\UnknownFormatException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage The requested format, 'no-such-format', is not available.
     */
    function testBadFormat()
    {
        $this->assertFormattedOutputMatches('Will fail, not return', 'no-such-format', ['a' => 'b']);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\IncompatibleDataException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage Data provided to Consolidation\OutputFormatters\Formatters\CsvFormatter must be one of an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields, an instance of Consolidation\OutputFormatters\StructuredData\AssociativeList or an array. Instead, a string was provided.
     */
    function testBadDataTypeForCsv()
    {
        $this->assertFormattedOutputMatches('Will fail, not return', 'csv', 'String cannot be converted to csv');
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\IncompatibleDataException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage Data provided to Consolidation\OutputFormatters\Formatters\JsonFormatter must be an array. Instead, a string was provided.
     */
    function testBadDataTypeForJson()
    {
        $this->assertFormattedOutputMatches('Will fail, not return', 'json', 'String cannot be converted to json');
    }

    function testNoFormatterSelected()
    {
        $data = 'Hello';
        $expected = $data;
        $this->assertFormattedOutputMatches($expected, '', $data);
    }

    function testRenderTableAsString()
    {
        $data = new RowsOfFields([['f1' => 'A', 'f2' => 'B', 'f3' => 'C'], ['f1' => 'x', 'f2' => 'y', 'f3' => 'z']]);
        $expected = "A\tB\tC\nx\ty\tz";

        $this->assertFormattedOutputMatches($expected, 'string', $data);
    }

    function testRenderTableAsStringWithSingleField()
    {
        $data = new RowsOfFields([['f1' => 'q', 'f2' => 'r', 'f3' => 's'], ['f1' => 'x', 'f2' => 'y', 'f3' => 'z']]);
        $expected = "q\nx";

        $options = new FormatterOptions([FormatterOptions::DEFAULT_STRING_FIELD => 'f1']);

        $this->assertFormattedOutputMatches($expected, 'string', $data, $options);
    }

    function testRenderTableAsStringWithSingleFieldAndUserSelectedField()
    {
        $data = new RowsOfFields([['f1' => 'q', 'f2' => 'r', 'f3' => 's'], ['f1' => 'x', 'f2' => 'y', 'f3' => 'z']]);
        $expected = "r\ny";

        $options = new FormatterOptions([FormatterOptions::DEFAULT_STRING_FIELD => 'f1']);

        $this->assertFormattedOutputMatches($expected, 'string', $data, $options, ['fields' => 'f2']);
    }

    function testSimpleCsv()
    {
        $data = ['a', 'b', 'c'];
        $expected = "a,b,c";

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testLinesOfCsv()
    {
        $data = [['a', 'b', 'c'], ['x', 'y', 'z']];
        $expected = "a,b,c\nx,y,z";

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testCsvWithEscapedValues()
    {
        $data = ["Red apple", "Yellow lemon"];
        $expected = '"Red apple","Yellow lemon"';

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testCsvWithEmbeddedSingleQuote()
    {
        $data = ["John's book", "Mary's laptop"];
        $expected = <<<EOT
"John's book","Mary's laptop"
EOT;

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testCsvWithEmbeddedDoubleQuote()
    {
        $data = ['The "best" solution'];
        $expected = <<<EOT
"The ""best"" solution"
EOT;

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testCsvBothKindsOfQuotes()
    {
        $data = ["John's \"new\" book", "Mary's \"modified\" laptop"];
        $expected = <<<EOT
"John's ""new"" book","Mary's ""modified"" laptop"
EOT;

        $this->assertFormattedOutputMatches($expected, 'csv', $data);
    }

    function testSimpleTsv()
    {
        $data = ['a', 'b', 'c'];
        $expected = "a\tb\tc";

        $this->assertFormattedOutputMatches($expected, 'tsv', $data);
    }

    function testLinesOfTsv()
    {
        $data = [['a', 'b', 'c'], ['x', 'y', 'z']];
        $expected = "a\tb\tc\nx\ty\tz";

        $this->assertFormattedOutputMatches($expected, 'tsv', $data);
    }

    function testTsvBothKindsOfQuotes()
    {
        $data = ["John's \"new\" book", "Mary's \"modified\" laptop"];
        $expected = "John's \"new\" book\tMary's \"modified\" laptop";

        $this->assertFormattedOutputMatches($expected, 'tsv', $data);
    }

    function testTsvWithEscapedValues()
    {
        $data = ["Red apple", "Yellow lemon", "Embedded\ttab"];
        $expected = "Red apple\tYellow lemon\tEmbedded\\ttab";

        $this->assertFormattedOutputMatches($expected, 'tsv', $data);
    }

    protected function missingCellTableExampleData()
    {
        $data = [
            [
                'one' => 'a',
                'two' => 'b',
                'three' => 'c',
            ],
            [
                'one' => 'x',
                'three' => 'z',
            ],
        ];
        return new RowsOfFields($data);
    }

    function testTableWithMissingCell()
    {
        $data = $this->missingCellTableExampleData();

        $expected = <<<EOT
 ----- ----- -------
  One   Two   Three
 ----- ----- -------
  a     b     c
  x           z
 ----- ----- -------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data);

        $expectedCsv = <<<EOT
One,Two,Three
a,b,c
x,,z
EOT;
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data);

        $expectedTsv = <<<EOT
a\tb\tc
x\t\tz
EOT;
        $this->assertFormattedOutputMatches($expectedTsv, 'tsv', $data);

        $expectedTsvWithHeaders = <<<EOT
One\tTwo\tThree
a\tb\tc
x\t\tz
EOT;
        $this->assertFormattedOutputMatches($expectedTsvWithHeaders, 'tsv', $data, new FormatterOptions(), ['include-field-labels' => true]);
    }

    protected function simpleTableExampleData()
    {
        $data = [
            'id-123' =>
            [
                'one' => 'a',
                'two' => 'b',
                'three' => 'c',
            ],
            'id-456' =>
            [
                'one' => 'x',
                'two' => 'y',
                'three' => 'z',
            ],
        ];
        return new RowsOfFields($data);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\InvalidFormatException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage The format table cannot be used with the data produced by this command, which was an array.  Valid formats are: csv,json,list,php,print-r,string,tsv,var_export,xml,yaml
     */
    function testIncompatibleDataForTableFormatter()
    {
        $data = $this->simpleTableExampleData()->getArrayCopy();
        $this->assertFormattedOutputMatches('Should throw an exception before comparing the table data', 'table', $data);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\InvalidFormatException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage The format sections cannot be used with the data produced by this command, which was an array.  Valid formats are: csv,json,list,php,print-r,string,tsv,var_export,xml,yaml
     */
    function testIncompatibleDataForSectionsFormatter()
    {
        $data = $this->simpleTableExampleData()->getArrayCopy();
        $this->assertFormattedOutputMatches('Should throw an exception before comparing the table data', 'sections', $data);
    }

    function testSimpleTable()
    {
        $data = $this->simpleTableExampleData();

        $expected = <<<EOT
 ----- ----- -------
  One   Two   Three
 ----- ----- -------
  a     b     c
  x     y     z
 ----- ----- -------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data);

        $expectedBorderless = <<<EOT
 ===== ===== =======
  One   Two   Three
 ===== ===== =======
  a     b     c
  x     y     z
 ===== ===== =======
EOT;
        $this->assertFormattedOutputMatches($expectedBorderless, 'table', $data, new FormatterOptions(['table-style' => 'borderless']));

        $expectedJson = <<<EOT
{
    "id-123": {
        "one": "a",
        "two": "b",
        "three": "c"
    },
    "id-456": {
        "one": "x",
        "two": "y",
        "three": "z"
    }
}
EOT;
        $this->assertFormattedOutputMatches($expectedJson, 'json', $data);

        $expectedCsv = <<<EOT
One,Two,Three
a,b,c
x,y,z
EOT;
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data);

        $expectedList = <<<EOT
id-123
id-456
EOT;
        $this->assertFormattedOutputMatches($expectedList, 'list', $data);
    }

    protected function tableWithAlternativesExampleData()
    {
        $data = [
            'id-123' =>
            [
                'one' => 'a',
                'two' => ['this', 'that', 'the other thing'],
                'three' => 'c',
            ],
            'id-456' =>
            [
                'one' => 'x',
                'two' => 'y',
                'three' => ['apples', 'oranges'],
            ],
        ];
        return new RowsOfFieldsWithAlternatives($data);
    }

    function testTableWithAlternatives()
    {
        $data = $this->tableWithAlternativesExampleData();

        $expected = <<<EOT
 ----- --------------------------- ----------------
  One   Two                         Three
 ----- --------------------------- ----------------
  a     this|that|the other thing   c
  x     y                           apples|oranges
 ----- --------------------------- ----------------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data);

        $expectedBorderless = <<<EOT
 ===== =========================== ================
  One   Two                         Three
 ===== =========================== ================
  a     this|that|the other thing   c
  x     y                           apples|oranges
 ===== =========================== ================
EOT;
        $this->assertFormattedOutputMatches($expectedBorderless, 'table', $data, new FormatterOptions(['table-style' => 'borderless']));

        $expectedJson = <<<EOT
{
    "id-123": {
        "one": "a",
        "two": [
            "this",
            "that",
            "the other thing"
        ],
        "three": "c"
    },
    "id-456": {
        "one": "x",
        "two": "y",
        "three": [
            "apples",
            "oranges"
        ]
    }
}
EOT;
        $this->assertFormattedOutputMatches($expectedJson, 'json', $data);

        $expectedCsv = <<<EOT
One,Two,Three
a,"this|that|the other thing",c
x,y,apples|oranges
EOT;
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data);

        $expectedList = <<<EOT
id-123
id-456
EOT;
        $this->assertFormattedOutputMatches($expectedList, 'list', $data);
    }

    function testSimpleTableWithFieldLabels()
    {
        $data = $this->simpleTableExampleData();
        $configurationData = new FormatterOptions(
            [
                'field-labels' => ['one' => 'Ichi', 'two' => 'Ni', 'three' => 'San'],
                'row-labels' => ['id-123' => 'Walrus', 'id-456' => 'Carpenter'],
            ]
        );
        $configurationDataAnnotationFormat = new FormatterOptions(
            [
                'field-labels' => "one: Uno\ntwo: Dos\nthree: Tres",
            ]
        );

        $expected = <<<EOT
 ------ ---- -----
  Ichi   Ni   San
 ------ ---- -----
  a      b    c
  x      y    z
 ------ ---- -----
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data, $configurationData);

        $expectedSidewaysTable = <<<EOT
 ------ --- ---
  Ichi   a   x
  Ni     b   y
  San    c   z
 ------ --- ---
EOT;
        $this->assertFormattedOutputMatches($expectedSidewaysTable, 'table', $data, $configurationData->override(['list-orientation' => true]));

        $expectedAnnotationFormatConfigData = <<<EOT
 ----- ----- ------
  Uno   Dos   Tres
 ----- ----- ------
  a     b     c
  x     y     z
 ----- ----- ------
EOT;
        $this->assertFormattedOutputMatches($expectedAnnotationFormatConfigData, 'table', $data, $configurationDataAnnotationFormat);

        $expectedWithNoFields = <<<EOT
 --- --- ---
  a   b   c
  x   y   z
 --- --- ---
EOT;
        $this->assertFormattedOutputMatches($expectedWithNoFields, 'table', $data, $configurationData, ['include-field-labels' => false]);

        $expectedWithReorderedFields = <<<EOT
 ----- ------
  San   Ichi
 ----- ------
  c     a
  z     x
 ----- ------
EOT;
        $this->assertFormattedOutputMatches($expectedWithReorderedFields, 'table', $data, $configurationData, ['fields' => ['three', 'one']]);
        $this->assertFormattedOutputMatches($expectedWithReorderedFields, 'table', $data, $configurationData, ['fields' => ['San', 'Ichi']]);
        $this->assertFormattedOutputMatches($expectedWithReorderedFields, 'table', $data, $configurationData, ['fields' => 'San,Ichi']);

        $expectedWithRegexField = <<<EOT
 ------ -----
  Ichi   San
 ------ -----
  a      c
  x      z
 ------ -----
EOT;
        $this->assertFormattedOutputMatches($expectedWithRegexField, 'table', $data, $configurationData, ['fields' => ['/e$/']]);
        $this->assertFormattedOutputMatches($expectedWithRegexField, 'table', $data, $configurationData, ['fields' => ['*e']]);
        $this->assertFormattedOutputMatches($expectedWithRegexField, 'table', $data, $configurationData, ['default-fields' => ['*e']]);

        $expectedSections = <<<EOT

Walrus
 One   a
 Two   b
 Three c

Carpenter
 One   x
 Two   y
 Three z
EOT;
        $this->assertFormattedOutputMatches($expectedSections, 'sections', $data, $configurationData);

        $expectedJson = <<<EOT
{
    "id-123": {
        "three": "c",
        "one": "a"
    },
    "id-456": {
        "three": "z",
        "one": "x"
    }
}
EOT;
        $this->assertFormattedOutputMatches($expectedJson, 'json', $data, $configurationData, ['fields' => ['San', 'Ichi']]);

        $expectedSingleField = <<<EOT
 -----
  San
 -----
  c
  z
 -----
EOT;
        $this->assertFormattedOutputMatches($expectedSingleField, 'table', $data, $configurationData, ['field' => 'San']);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\UnknownFieldException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage The requested field, 'Shi', is not defined.
     */
    function testNoSuchFieldException()
    {
        $configurationData = new FormatterOptions(
            [
                'field-labels' => ['one' => 'Ichi', 'two' => 'Ni', 'three' => 'San'],
                'row-labels' => ['id-123' => 'Walrus', 'id-456' => 'Carpenter'],
            ]
        );
        $data = $this->simpleTableExampleData();
        $this->assertFormattedOutputMatches('Will throw before comparing', 'table', $data, $configurationData, ['field' => 'Shi']);
    }

    protected function simpleListExampleData()
    {
        $data = [
            'one' => 'apple',
            'two' => 'banana',
            'three' => 'carrot',
        ];
        return new AssociativeList($data);
    }

    /**
     * @expectedException \Consolidation\OutputFormatters\Exception\InvalidFormatException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage The format table cannot be used with the data produced by this command, which was an array.  Valid formats are: csv,json,list,php,print-r,string,tsv,var_export,xml,yaml
     */
    function testIncompatibleListDataForTableFormatter()
    {
        $data = $this->simpleListExampleData();
        $this->assertFormattedOutputMatches('Should throw an exception before comparing the table data', 'table', $data->getArrayCopy());
    }


    function testSimpleList()
    {
        $data = $this->simpleListExampleData();

        $expected = <<<EOT
 ------- --------
  One     apple
  Two     banana
  Three   carrot
 ------- --------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data);

        $expected = <<<EOT
 ----- --------
  I     apple
  II    banana
  III   carrot
 ----- --------
EOT;
        // If we provide field labels, then the output will change to reflect that.
        $formatterOptionsWithFieldLables = new FormatterOptions();
        $formatterOptionsWithFieldLables
            ->setFieldLabels(['one' => 'I', 'two' => 'II', 'three' => 'III']);
        $this->assertFormattedOutputMatches($expected, 'table', $data, $formatterOptionsWithFieldLables);

        // Adding an extra field that does not exist in the data set should not change the output
        $formatterOptionsWithExtraFieldLables = new FormatterOptions();
        $formatterOptionsWithExtraFieldLables
            ->setFieldLabels(['one' => 'I', 'two' => 'II', 'three' => 'III', 'four' => 'IV']);
        $this->assertFormattedOutputMatches($expected, 'table', $data, $formatterOptionsWithExtraFieldLables);

        $expectedRotated = <<<EOT
 ------- -------- --------
  One     Two      Three
 ------- -------- --------
  apple   banana   carrot
 ------- -------- --------
EOT;
        $this->assertFormattedOutputMatches($expectedRotated, 'table', $data, new FormatterOptions(['list-orientation' => false]));

        $expectedList = <<< EOT
apple
banana
carrot
EOT;
        $this->assertFormattedOutputMatches($expectedList, 'list', $data);

        $expectedReorderedList = <<< EOT
carrot
apple
EOT;
        $options = new FormatterOptions([FormatterOptions::FIELDS => 'three,one']);
        $this->assertFormattedOutputMatches($expectedReorderedList, 'list', $data, $options);

        $expectedCsv = <<< EOT
One,Two,Three
apple,banana,carrot
EOT;
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data);

        $expectedCsvNoHeaders = 'apple,banana,carrot';
        $this->assertFormattedOutputMatches($expectedCsvNoHeaders, 'csv', $data, new FormatterOptions(), ['include-field-labels' => false]);

        // Next, configure the formatter options with 'include-field-labels',
        // but set --include-field-labels to turn the option back on again.
        $options = new FormatterOptions(['include-field-labels' => false]);
        $input = new StringInput('test --include-field-labels');
        $optionDefinitions = [
            new InputArgument('unused', InputArgument::REQUIRED),
            new InputOption('include-field-labels', null, InputOption::VALUE_NONE),
        ];
        $definition = new InputDefinition($optionDefinitions);
        $input->bind($definition);
        $testValue = $input->getOption('include-field-labels');
        $this->assertTrue($testValue);
        $hasFieldLabels = $input->hasOption('include-field-labels');
        $this->assertTrue($hasFieldLabels);

        $this->assertFormattedOutputMatches($expectedCsvNoHeaders, 'csv', $data, $options);
        $options->setInput($input);
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data, $options);
    }

    protected function associativeListWithRenderer()
    {
        $data = [
            'one' => 'apple',
            'two' => ['banana', 'plantain'],
            'three' => 'carrot',
            'four' => ['peaches', 'pumpkin pie'],
        ];
        $list = new AssociativeList($data);

        $list->addRendererFunction(
            function ($key, $cellData, FormatterOptions $options)
            {
                if (is_array($cellData)) {
                    return implode(',', $cellData);
                }
                return $cellData;
            }
        );

        return $list;
    }

    protected function associativeListWithCsvCells()
    {
        $data = [
            'one' => 'apple',
            'two' => ['banana', 'plantain'],
            'three' => 'carrot',
            'four' => ['peaches', 'pumpkin pie'],
        ];
        return new AssociativeListWithCsvCells($data);
    }

    function testAssociativeListWithCsvCells()
    {
        $this->doAssociativeListWithCsvCells($this->associativeListWithRenderer());
        $this->doAssociativeListWithCsvCells($this->associativeListWithCsvCells());
    }

    function doAssociativeListWithCsvCells($data)
    {
        $expected = <<<EOT
 ------- ---------------------
  One     apple
  Two     banana,plantain
  Three   carrot
  Four    peaches,pumpkin pie
 ------- ---------------------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data);

        $expectedList = <<< EOT
apple
banana,plantain
carrot
peaches,pumpkin pie
EOT;
        $this->assertFormattedOutputMatches($expectedList, 'list', $data);

        $expectedCsv = <<< EOT
One,Two,Three,Four
apple,"banana,plantain",carrot,"peaches,pumpkin pie"
EOT;
        $this->assertFormattedOutputMatches($expectedCsv, 'csv', $data);

        $expectedCsvNoHeaders = 'apple,"banana,plantain",carrot,"peaches,pumpkin pie"';
        $this->assertFormattedOutputMatches($expectedCsvNoHeaders, 'csv', $data, new FormatterOptions(), ['include-field-labels' => false]);
    }

    function testSimpleListWithFieldLabels()
    {
        $data = $this->simpleListExampleData();
        $configurationData = new FormatterOptions(
            [
                'field-labels' => ['one' => 'Ichi', 'two' => 'Ni', 'three' => 'San'],
            ]
        );

        $expected = <<<EOT
 ------ --------
  Ichi   apple
  Ni     banana
  San    carrot
 ------ --------
EOT;
        $this->assertFormattedOutputMatches($expected, 'table', $data, $configurationData);

        $expectedWithReorderedFields = <<<EOT
 ------ --------
  San    carrot
  Ichi   apple
 ------ --------
EOT;
        $this->assertFormattedOutputMatches($expectedWithReorderedFields, 'table', $data, $configurationData, ['fields' => ['three', 'one']]);
        $this->assertFormattedOutputMatches($expectedWithReorderedFields, 'table', $data, $configurationData, ['fields' => ['San', 'Ichi']]);

        $expectedJson = <<<EOT
{
    "three": "carrot",
    "one": "apple"
}
EOT;
        $this->assertFormattedOutputMatches($expectedJson, 'json', $data, $configurationData, ['fields' => ['San', 'Ichi']]);
    }

    function testSimpleXml()
    {
        $data = [
            'name' => 'primary',
            'description' => 'The primary colors of the color wheel.',
            'colors' =>
            [
                'red',
                'yellow',
                'blue',
            ],
        ];

        $expected = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<document name="primary">
  <description>The primary colors of the color wheel.</description>
  <colors>
    <color>red</color>
    <color>yellow</color>
    <color>blue</color>
  </colors>
</document>
EOT;

        $this->assertFormattedOutputMatches($expected, 'xml', $data);
    }

    function domDocumentData()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $document = $dom->createElement('document');
        $dom->appendChild($document);

        $document->setAttribute('name', 'primary');
        $description = $dom->createElement('description');
        $document->appendChild($description);
        $description->appendChild($dom->createTextNode('The primary colors of the color wheel.'));

        $this->domCreateElements($dom, $document, 'color', ['red', 'yellow', 'blue']);

        return $dom;
    }

    function domCreateElements($dom, $element, $name, $data)
    {
        $container = $dom->createElement("{$name}s");
        $element->appendChild($container);
        foreach ($data as $value) {
            $child = $dom->createElement($name);
            $container->appendChild($child);
            $child->appendChild($dom->createTextNode($value));
        }
    }

    function complexDomDocumentData()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $document = $dom->createElement('document');
        $dom->appendChild($document);

        $document->setAttribute('name', 'widget-collection');
        $description = $dom->createElement('description');
        $document->appendChild($description);
        $description->appendChild($dom->createTextNode('A couple of widgets.'));

        $widgets = $dom->createElement('widgets');
        $document->appendChild($widgets);

        $widget = $dom->createElement('widget');
        $widgets->appendChild($widget);
        $widget->setAttribute('name', 'usual');
        $this->domCreateElements($dom, $widget, 'color', ['red', 'yellow', 'blue']);
        $this->domCreateElements($dom, $widget, 'shape', ['square', 'circle', 'triangle']);

        $widget = $dom->createElement('widget');
        $widgets->appendChild($widget);
        $widget->setAttribute('name', 'unusual');
        $this->domCreateElements($dom, $widget, 'color', ['muave', 'puce', 'umber']);
        $this->domCreateElements($dom, $widget, 'shape', ['elipse', 'rhombus', 'trapazoid']);

        return $dom;
    }

    function domDocumentTestValues()
    {

        $expectedXml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<document name="primary">
  <description>The primary colors of the color wheel.</description>
  <colors>
    <color>red</color>
    <color>yellow</color>
    <color>blue</color>
  </colors>
</document>
EOT;

        $expectedJson = <<<EOT
{
    "name": "primary",
    "description": "The primary colors of the color wheel.",
    "colors": [
        "red",
        "yellow",
        "blue"
    ]
}
EOT;

        $expectedComplexXml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<document name="widget-collection">
  <description>A couple of widgets.</description>
  <widgets>
    <widget name="usual">
      <colors>
        <color>red</color>
        <color>yellow</color>
        <color>blue</color>
      </colors>
      <shapes>
        <shape>square</shape>
        <shape>circle</shape>
        <shape>triangle</shape>
      </shapes>
    </widget>
    <widget name="unusual">
      <colors>
        <color>muave</color>
        <color>puce</color>
        <color>umber</color>
      </colors>
      <shapes>
        <shape>elipse</shape>
        <shape>rhombus</shape>
        <shape>trapazoid</shape>
      </shapes>
    </widget>
  </widgets>
</document>
EOT;

        $expectedComplexJson = <<<EOT
{
    "name": "widget-collection",
    "description": "A couple of widgets.",
    "widgets": [
        {
            "name": "usual",
            "colors": [
                "red",
                "yellow",
                "blue"
            ],
            "shapes": [
                "square",
                "circle",
                "triangle"
            ]
        },
        {
            "name": "unusual",
            "colors": [
                "muave",
                "puce",
                "umber"
            ],
            "shapes": [
                "elipse",
                "rhombus",
                "trapazoid"
            ]
        }
    ]
}
EOT;

        return [
            [
                $this->domDocumentData(),
                $expectedXml,
                $expectedJson,
            ],
            [
                $this->complexDomDocumentData(),
                $expectedComplexXml,
                $expectedComplexJson,
            ],
        ];
    }

    /**
     *  @dataProvider domDocumentTestValues
     */
    function testDomData($data, $expectedXml, $expectedJson)
    {
        $this->assertFormattedOutputMatches($expectedXml, 'xml', $data);
        $this->assertFormattedOutputMatches($expectedJson, 'json', $data);

        // Check to see if we get the same xml data if we convert from
        // DOM -> array -> DOM.
        $expectedJsonAsArray = (array)json_decode($expectedJson);
        $this->assertFormattedOutputMatches($expectedXml, 'xml', $expectedJsonAsArray);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 1
     * @expectedExceptionMessage Data provided to Consolidation\OutputFormatters\Formatters\XmlFormatter must be either an instance of DOMDocument or an array. Instead, a string was provided.
     */
    function testDataTypeForXmlFormatter()
    {
        $this->assertFormattedOutputMatches('Will fail, not return', 'xml', 'Strings cannot be converted to XML');
    }
}
