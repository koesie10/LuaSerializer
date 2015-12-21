<?php
/**
 * TableASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:34
 */

namespace Vlaswinkel\Lua\AST;

class TableASTNode extends ASTNode {
    const NAME = 'table';

    /**
     * @var TableEntryASTNode[]
     */
    private $entries;

    /**
     * TableASTNode constructor.
     *
     * @param TableEntryASTNode[] $entries
     */
    public function __construct(array $entries) {
        $this->entries = $entries;
    }


    /**
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * @return TableEntryASTNode[]
     */
    public function getEntries() {
        return $this->entries;
    }
}