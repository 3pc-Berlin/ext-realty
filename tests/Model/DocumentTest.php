<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Bernd Schönbach <bernd.schoenbach@googlemail.com>
 */
class tx_realty_Model_DocumentTest extends Tx_Phpunit_TestCase
{
    /**
     * @var tx_realty_Model_Document
     */
    private $fixture = null;

    protected function setUp()
    {
        $this->fixture = new tx_realty_Model_Document();
    }

    /*
     * Tests concerning the title
     */

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $this->fixture->setData(array('title' => 'Just another document'));

        self::assertEquals(
            'Just another document',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->fixture->setTitle('Just another document');

        self::assertEquals(
            'Just another document',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setTitleForEmptyTitleThrowsException()
    {
        $this->fixture->setTitle('');
    }

    ////////////////////////////////////////////
    // Tests concerning the document file name
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getFileNameReturnsDocumentFileName()
    {
        $this->fixture->setData(array('filename' => 'foo.pdf'));

        self::assertEquals(
            'foo.pdf',
            $this->fixture->getFileName()
        );
    }

    /**
     * @test
     */
    public function setFileNameSetsFileName()
    {
        $this->fixture->setFileName('bar.pdf');

        self::assertEquals(
            'bar.pdf',
            $this->fixture->getFileName()
        );
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function setFileNameForEmptyFileNameThrowsException()
    {
        $this->fixture->setFileName('');
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the relation to the realty object
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getObjectReturnsObject()
    {
        $realtyObject = new tx_realty_Model_RealtyObject();
        $this->fixture->setData(array('object' => $realtyObject));

        self::assertSame(
            $realtyObject,
            $this->fixture->getObject()
        );
    }

    /**
     * @test
     */
    public function setObjectSetsObject()
    {
        $realtyObject = new tx_realty_Model_RealtyObject();
        $this->fixture->setObject($realtyObject);

        self::assertSame(
            $realtyObject,
            $this->fixture->getObject()
        );
    }

    /////////////////////////////////
    // Tests concerning the sorting
    /////////////////////////////////

    /**
     * @test
     */
    public function getSortingInitiallyReturnsZero()
    {
        $this->fixture->setData(array());

        self::assertEquals(
            0,
            $this->fixture->getSorting()
        );
    }

    /**
     * @test
     */
    public function getSortingReturnsSorting()
    {
        $this->fixture->setData(array('sorting' => 42));

        self::assertEquals(
            42,
            $this->fixture->getSorting()
        );
    }

    /**
     * @test
     */
    public function setSortingSetsSorting()
    {
        $this->fixture->setSorting(21);

        self::assertEquals(
            21,
            $this->fixture->getSorting()
        );
    }
}
