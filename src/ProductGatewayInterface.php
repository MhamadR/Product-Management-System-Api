<?php

namespace TestAssignment\src;

interface ProductGatewayInterface
{
    public function getAll(): array;
    public function deleteProducts(string $idList): array;
    public function createProduct($data): string;
    public function productExists(string $sku): bool;
    public function lookupProductTypeId(string $productType): int;
    public function lookupAttributeId(string $attributeName): int;
    public function getTypes(): array;
    public function getAttributes(): array;
}
