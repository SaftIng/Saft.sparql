<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Test\TestCase;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\UpdateQueryImpl;

class UpdateQueryImplTest extends TestCase
{
    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = new UpdateQueryImpl(null, new RdfHelpers());
        $this->queryFactory = new QueryFactoryImpl(new RdfHelpers());
    }

    /*
     * Tests for constructor
     */

    public function testConstructor()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }',
            new RdfHelpers()
        );

        $this->assertEquals(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }',
            $this->fixture->getQuery()
        );
    }

    public function testConstructorDeleteData()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE DATA { ?x foaf:name "Alice" }',
            new RdfHelpers()
        );

        $this->assertEquals(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE DATA { ?x foaf:name "Alice" }',
            $this->fixture->getQuery()
        );
    }

    /*
     * Tests for extractGraphs
     */

    public function testExtractGraphsDeleteData()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA {
                Graph <http://saft/test/g1> {<http://saft/test/s1> dc:p1 <http://saft/test/o1>}
                Graph <http://saft/test/g2> {<http://saft/test/s1> dc:p1 <http://saft/test/o1>}
            }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            ['http://saft/test/g1', 'http://saft/test/g2'],
            $queryParts['graphs']
        );
    }

    public function testExtractGraphsInsertIntoGraph()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX dc: <http://foo/bar/>
            INSERT DATA { Graph <http://saft/test/g1> {
                <http://saft/test/s1> dc:p1 <http://saft/test/o1>}
            }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(['http://saft/test/g1'], $queryParts['graphs']);
    }

    /*
     * Tests for extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA { GRAPH <http://> { ?s dc: ?o. ?s <http://foo/sss> ?o } }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(['ns-0' => 'http://foo/'], $queryParts['namespaces']);
    }

    public function testExtractNamespacesFromQueryNoNamespaces()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA { GRAPH <http://> { ?s ?p ?o } }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['namespaces']));
    }

    /*
     * Tests for extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        // assumption here is that fixture is of type
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(['foaf' => 'http://xmlns.com/foaf/0.1/'], $queryParts['prefixes']);
    }

    public function testExtractPrefixesFromQueryNoPrefixes()
    {
        // assumption here is that fixture is of type
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'DELETE DATA { GRAPH <http://> { ?s ?p ?o } }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['prefixes']));
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetSubTypeDeleteData()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX dc: <http://foo/bar/> DELETE DATA { GRAPH <http://> { ?s ?p ?o } }',
            new RdfHelpers()
        );

        $this->assertEquals('deleteData', $this->fixture->getSubType());
    }

    public function testGetSubTypeInsertData()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX dc: <http://foo/bar/> INSERT DATA { GRAPH <http://> { ?s dc:foo "hi" } }',
            new RdfHelpers()
        );

        $this->assertEquals('insertData', $this->fixture->getSubType());
    }

    public function testGetSubTypeInsertInto()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX dc: <http://foo/bar/> INSERT INTO GRAPH <http://> { ?s dc:foo "hi" }',
            new RdfHelpers()
        );

        $this->assertEquals('insertInto', $this->fixture->getSubType());
    }

    public function testGetSubTypeWithDeleteInsertWhere()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX dc: <http://foo/bar/>
             WITH <http://> DELETE { ?s dc:foo "hi" } INSERT { ?s dc:foo "ho" } WHERE { ?s dc:foo "hi" }',
            new RdfHelpers()
        );

        $this->assertEquals('withDeleteInsertWhere', $this->fixture->getSubType());
    }

    public function testGetSubTypeWithDeleteWhere()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX dc: <http://foo/bar/> WITH <http://> DELETE { ?s dc:foo "hi" } WHERE { ?s dc:foo "hi" }',
            new RdfHelpers()
        );

        $this->assertEquals('withDeleteWhere', $this->fixture->getSubType());
    }

    /*
     * Tests for getQueryParts
     */

    public function testGetQueryPartsEverything()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/>
            DELETE { ?x foaf:name "Alice"^^<http://www.w3.org/2001/XMLSchema#string>. ?x <http://namespace/aa> ?y }
            WHERE { ?s ?p ?o. FILTER(?o < 40) }',
            new RdfHelpers()
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(9, count($queryParts));

        $this->assertEquals(
            '?x foaf:name "Alice"^^<http://www.w3.org/2001/XMLSchema#string>. ?x <http://namespace/aa> ?y',
            $queryParts['deleteData']
        );
        $this->assertEquals('?s ?p ?o. FILTER(?o < 40)', $queryParts['deleteWhere']);
        $this->assertEquals(
            [
                [
                    'type' => 'expression',
                    'sub_type' => 'relational',
                    'patterns' => [
                        [
                            'value' => 'o',
                            'type' => 'var',
                            'operator' => '',
                        ],
                        [
                            'value' => '40',
                            'type' => 'literal',
                            'operator' => '',
                            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer',
                        ],
                    ],
                    'operator' => '<',
                ],
            ],
            $queryParts['filter_pattern']
        );
        $this->assertEquals(['http://graph/'], $queryParts['graphs']);
        $this->assertEquals(
            ['ns-0' => 'http://namespace/', 'xsd' => 'http://www.w3.org/2001/XMLSchema#'],
            $queryParts['namespaces']
        );
        $this->assertEquals(['foaf' => 'http://xmlns.com/foaf/0.1/'], $queryParts['prefixes']);
        $this->assertEquals('withDeleteWhere', $queryParts['sub_type']);
        $this->assertEquals(
            [
                [
                    's' => 'x',
                    'p' => 'http://xmlns.com/foaf/0.1/name',
                    'o' => 'Alice',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'typed-literal',
                    'o_datatype' => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang' => null,
                ],
                [
                    's' => 'x',
                    'p' => 'http://namespace/aa',
                    'o' => 'y',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null,
                ],
                [
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null,
                ],
            ],
            $queryParts['triple_pattern']
        );
        $this->assertEqualsArrays(['s', 'p', 'o', 'x', 'y'], $queryParts['variables']);
    }

    // test if it recognizes the query right
    public function testGetQueryPartsWithDeleteWhereStatement()
    {
        $this->fixture = new UpdateQueryImpl(
            'PREFIX backmodel: <https://back/model/>
            WITH <https://data/>
            DELETE { ?settings_blank ?p ?o . }
            WHERE { <https://foo> backmodel:has-user-settings ?settings_blank . }',
            new RdfHelpers()
        );

        $this->assertTrue(0 < $this->fixture->getQueryParts());
    }

    public function testGetQueryPartsInsertDataMissingDataPart()
    {
        $this->fixture = new UpdateQueryImpl('INSERT DATA { }', new RdfHelpers());

        // expects an exception because data part is empty
        $this->setExpectedException('\Exception');
        $this->fixture->getQueryParts();
    }

    /*
     * Tests for isAskQuery
     */

    public function testIsAskQuery()
    {
        $this->assertFalse($this->fixture->isAskQuery());
    }

    /*
     * Tests for isDescribeQuery
     */

    public function testIsDescribeQuery()
    {
        $this->assertFalse($this->fixture->isDescribeQuery());
    }

    /*
     * Tests for isGraphQuery
     */

    public function testIsGraphQuery()
    {
        $this->assertFalse($this->fixture->isGraphQuery());
    }

    /*
     * Tests for isSelectQuery
     */

    public function testIsSelectQuery()
    {
        $this->assertFalse($this->fixture->isSelectQuery());
    }

    /*
     * Tests for isUpdateQuery
     */

    public function testIsUpdateQuery()
    {
        $this->assertTrue($this->fixture->isUpdateQuery());
    }
}
