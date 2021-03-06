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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_realty_Model_ImageTest extends Tx_Phpunit_TestCase
{
    /**
     * @var tx_realty_Model_Image
     */
    private $fixture = null;

    protected function setUp()
    {
        $this->fixture = new tx_realty_Model_Image();
    }

    /*
     * Tests concerning the title
     */

    /**
     * @test
     */
    public function getTitleReturnsCaption()
    {
        $this->fixture->setData(array('caption' => 'Just another room'));

        self::assertEquals(
            'Just another room',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->fixture->setTitle('Just another room');

        self::assertEquals(
            'Just another room',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleForEmptyTitleSetsEmptyTitle()
    {
        $this->fixture->setTitle('');

        self::assertEquals(
            '',
            $this->fixture->getTitle()
        );
    }

    /////////////////////////////////////////
    // Tests concerning the image file name
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getFileNameReturnsImageFileName()
    {
        $this->fixture->setData(array('image' => 'foo.jpg'));

        self::assertEquals(
            'foo.jpg',
            $this->fixture->getFileName()
        );
    }

    /**
     * @test
     */
    public function setFileNameSetsFileName()
    {
        $this->fixture->setFileName('bar.jpg');

        self::assertEquals(
            'bar.jpg',
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

    /////////////////////////////////////////////
    // Tests concerning the thumbnail file name
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getThumbnailFileNameReturnsThumbnailFileName()
    {
        $this->fixture->setData(array('thumbnail' => 'foo.jpg'));

        self::assertEquals(
            'foo.jpg',
            $this->fixture->getThumbnailFileName()
        );
    }

    /**
     * @test
     */
    public function setThumbnailFileNameSetsThumbnailFileName()
    {
        $this->fixture->setThumbnailFileName('bar.jpg');

        self::assertEquals(
            'bar.jpg',
            $this->fixture->getThumbnailFileName()
        );
    }

    /**
     * @test
     */
    public function setThumbnailFileNameCanSetThumbnailFileNameToEmptyString()
    {
        $this->fixture->setData(array('thumbnail' => 'foo.jpg'));
        $this->fixture->setThumbnailFileName('');

        self::assertEquals(
            '',
            $this->fixture->getThumbnailFileName()
        );
    }

    /**
     * @test
     */
    public function hasThumbnailFileNameForNoThumbnailReturnsFalse()
    {
        $this->fixture->setData(array());

        self::assertFalse(
            $this->fixture->hasThumbnailFileName()
        );
    }

    /**
     * @test
     */
    public function hasThumbnailFileNameForNonEmptyThumbnailReturnsFalse()
    {
        $this->fixture->setData(array('thumbnail' => 'foo.jpg'));

        self::assertTrue(
            $this->fixture->hasThumbnailFileName()
        );
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

    ///////////////////////////////////////////////
    // Tests concerning the position of the image
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getPositionWithoutDataSetReturnsZero()
    {
        $this->fixture->setData(array());

        self::assertEquals(
            0,
            $this->fixture->getPosition()
        );
    }

    /**
     * @test
     */
    public function getPositionWithPositionSetReturnsPosition()
    {
        $this->fixture->setData(array('position' => 1));

        self::assertEquals(
            1,
            $this->fixture->getPosition()
        );
    }

    /**
     * @test
     */
    public function setPositionSetsPosition()
    {
        $this->fixture->setPosition(5);

        self::assertEquals(
            5,
            $this->fixture->getPosition()
        );
    }
}
