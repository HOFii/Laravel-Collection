<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());
    }
    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            $this->assertEquals($key + 1, $value);
        }
    }
    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }
    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });
        $this->assertEqualsCanonicalizing([2, 4, 6], $result->all());
    }
    public function testMapInto()
    {
        $collection = collect(["Gusti"]);
        $result = $collection->mapInto(Person::class);
        $this->assertEquals([new Person("Gusti")], $result->all());
    }
    public function testMapSpread()
    {
        $collection = collect([
            ["Gusti", "Akbar"],
            ["Kiana", "Kaslana"]
        ]);

        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . ' ' . $lastName;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Gusti Akbar"),
            new Person("Kiana Kaslana"),
        ], $result->all());
    }
    public function testMapToGroups()
    {
        $collection = collect([
            [
                "name" => "Gusti",
                "department" => "IT"
            ],
            [
                "name" => "Kiana",
                "department" => "IT"
            ],
            [
                "name" => "Elaina",
                "department" => "HR"
            ]
        ]);

        $result = $collection->mapToGroups(function ($person) {
            return [
                $person["department"] => $person["name"]
            ];
        });

        $this->assertEquals([
            "IT" => collect(["Gusti", "Kiana"]),
            "HR" => collect(["Elaina"])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }
    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = collect(["name", "country"]);
        $collection2 = collect(["Gusti", "Indonesia"]);
        $collection3 = $collection1->combine($collection2);

        $this->assertEqualsCanonicalizing([
            "name" => "Gusti",
            "country" => "Indonesia"
        ], $collection3->all());
    }
    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        $result = $collection->collapse();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Gusti",
                "hobbies" => ["Coding", "Gaming"]
            ],
            [
                "name" => "Kiana",
                "hobbies" => ["Reading", "Writing"]
            ],
        ]);
        $result = $collection->flatMap(function ($item) {
            $hobbies = $item["hobbies"];
            return $hobbies;
        });

        $this->assertEqualsCanonicalizing(["Coding", "Gaming", "Reading", "Writing"], $result->all());
    }
    public function testStringRepresentation()
    {
        $collection = collect(["Gusti", "Alifiraqsha", "Akbar"]);

        $this->assertEquals("Gusti-Alifiraqsha-Akbar", $collection->join("-"));
        $this->assertEquals("Gusti-Alifiraqsha_Akbar", $collection->join("-", "_"));
        $this->assertEquals("Gusti, Alifiraqsha and Akbar", $collection->join(", ", " and "));
    }
    public function testFilter()
    {
        $collection = collect([
            "Gusti" => 100,
            "Kiana" => 80,
            "Elaina" => 90
        ]);

        $result = $collection->filter(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            "Gusti" => 100,
            "Elaina" => 90
        ], $result->all());
    }
    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });
        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }
    public function testPartition()
    {
        $collection = collect([
            "Gusti" => 100,
            "Kiana" => 80,
            "Elaina" => 90
        ]);

        [$result1, $result2] = $collection->partition(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            "Gusti" => 100,
            "Elaina" => 90
        ], $result1->all());
        $this->assertEquals([
            "Kiana" => 80
        ], $result2->all());
    }
    public function testTesting()
    {
        $collection = collect(["Gusti", "Alifiraqsha", "Akbar"]);
        $this->assertTrue($collection->contains("Gusti"));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == "Akbar";
        }));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "Gusti",
                "department" => "IT"
            ],
            [
                "name" => "Kiana",
                "department" => "IT"
            ],
            [
                "name" => "Elaina",
                "department" => "HR"
            ]
        ]);

        $result = $collection->groupBy("department");

        assertEquals([
            "IT" => collect([
                [
                    "name" => "Gusti",
                    "department" => "IT"
                ],
                [
                    "name" => "Kiana",
                    "department" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Elaina",
                    "department" => "HR"
                ]
            ])
        ], $result->all());

        $result = $collection->groupBy(function ($value, $key) {
            return strtolower($value["department"]);
        });

        assertEquals([
            "it" => collect([
                [
                    "name" => "Gusti",
                    "department" => "IT"
                ],
                [
                    "name" => "Kiana",
                    "department" => "IT"
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "Elaina",
                    "department" => "HR"
                ]
            ])
        ], $result->all());
    }
    public function testSlicing()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);

        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4, 5], $result->all());

    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 1, 2, 3, 1, 2, 3]);

        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }
    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

    }
}
