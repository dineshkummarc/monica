<?php

declare(strict_types=1);

namespace Bladestan\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Livewire extends Blade's compiler and injects runtime hooks into the compiled
 * output: `SupportCompiledWireKeys::processComponentKey()` / `openLoop()` and
 * friends, each guarded by `ExtendBlade::isRenderingLivewireComponent()`. These
 * are inert for static analysis (they touch no template variable) and their
 * exact shape changes between Livewire versions, so leaving them in would couple
 * the compiled output to whichever Livewire is installed. Strip them so a
 * template compiles to the same PHP regardless of the Livewire version.
 */
final class RemoveLivewireCompilerArtifacts extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): ?int
    {
        // The `if (ExtendBlade::isRenderingLivewireComponent()) { ... }` guard,
        // including whatever loop hook it wraps.
        if ($node instanceof If_
            && $this->isStaticCallTo($node->cond, 'ExtendBlade', 'isRenderingLivewireComponent')
        ) {
            return NodeTraverser::REMOVE_NODE;
        }

        // A bare `SupportCompiledWireKeys::processComponentKey($component);` call.
        if ($node instanceof Expression && $this->isStaticCallTo($node->expr, 'SupportCompiledWireKeys')) {
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }

    private function isStaticCallTo(Node $node, string $classNeedle, ?string $method = null): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->class instanceof Name || ! str_contains($node->class->toString(), $classNeedle)) {
            return false;
        }

        if ($method === null) {
            return true;
        }

        return $node->name instanceof Identifier && $node->name->toString() === $method;
    }
}
