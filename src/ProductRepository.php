<?php

namespace TestAssignment\src;

class ProductRepository implements ProductGatewayInterface
{
    public function __construct(private ProductGatewayInterface $gateway)
    {
    }

    public function getAll(): array
    {
        return $this->gateway->getAll();
    }

    public function deleteProducts(string $idList): array
    {
        return $this->gateway->deleteProducts($idList);
    }

    public function createProduct($data): string
    {
        return $this->gateway->createProduct($data);
    }

    public function productExists(string $sku): bool
    {
        return $this->gateway->productExists(($sku));
    }

    public function lookupProductTypeId(string $productType): int
    {
        return $this->gateway->lookupProductTypeId($productType);
    }

    public function lookupAttributeId(string $attributeName): int
    {
        return $this->gateway->lookupAttributeId($attributeName);
    }

    public function getTypes(): array
    {
        return $this->gateway->getTypes();
    }

    public function getAttributes(): array
    {
        return $this->gateway->getAttributes();
    }
}
