<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\Reader\CustomerGroupSuggestionReaderInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Reader
 * @group CustomerGroupSuggestionReaderTest
 * Add your own group annotations below this line
 */
class CustomerGroupSuggestionReaderTest extends Unit
{
    protected CustomerGroupCommunicationTester $tester;

    public function testGetSuggestionsBySearchTermReturnsTheMatchingGroupNamesSortedByName(): void
    {
        // Arrange
        $searchTerm = uniqid('suggest');
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => sprintf('%s b', $searchTerm)]);
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => sprintf('%s a', $searchTerm)]);
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => uniqid('other')]);

        // Act
        $suggestions = $this->createReader()->getSuggestionsBySearchTerm($searchTerm);

        // Assert
        $this->assertSame([
            ['id' => sprintf('%s a', $searchTerm), 'text' => sprintf('%s a', $searchTerm)],
            ['id' => sprintf('%s b', $searchTerm), 'text' => sprintf('%s b', $searchTerm)],
        ], $suggestions);
    }

    public function testGetSuggestionsBySearchTermReturnsNoMoreGroupsThanTheConfiguredLimit(): void
    {
        // Arrange
        $searchTerm = uniqid('suggest');
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => sprintf('%s 1', $searchTerm)]);
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => sprintf('%s 2', $searchTerm)]);
        $this->tester->haveCustomerGroup([CustomerGroupTransfer::NAME => sprintf('%s 3', $searchTerm)]);
        $this->tester->mockConfigMethod('getCustomerGroupSuggestionLimit', 2);

        // Act
        $suggestions = $this->createReader()->getSuggestionsBySearchTerm($searchTerm);

        // Assert
        $this->assertCount(2, $suggestions);
    }

    public function testGetSuggestionsBySearchTermReturnsNothingForABlankSearchTerm(): void
    {
        // Arrange
        $this->tester->haveCustomerGroup();

        // Act
        $suggestions = $this->createReader()->getSuggestionsBySearchTerm('   ');

        // Assert
        $this->assertSame([], $suggestions);
    }

    protected function createReader(): CustomerGroupSuggestionReaderInterface
    {
        return $this->tester->getFactory()->createCustomerGroupSuggestionReader();
    }
}
