<?php

namespace TestAssignment\src;

use PDO;

class ProductGateway implements ProductGatewayInterface
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT
                    p.id,
                    p.sku,
                    p.name,
                    p.price,
                    pt.type,
                    pa.attribute,
                    apa.value,
                    GROUP_CONCAT(pa.attribute) AS attributes,
                    GROUP_CONCAT(apa.value) AS attribute_values
                FROM
                    products p
                INNER JOIN
                    product_types pt ON p.product_type_id = pt.id
                INNER JOIN
                    product_attribute_associations apa ON p.id = apa.product_id
                INNER JOIN
                    product_attributes pa ON apa.attribute_id = pa.id
                GROUP BY
                    p.id";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attributes = explode(',', $row['attributes']);
            $values = explode(',', $row['attribute_values']);

            $productData = [
                'id' => $row['id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'price' => $row['price'],
                'type' => $row['type']
            ];

            // Add attributes and their values to the product data
            for ($i = 0; $i < count($attributes); $i++) {
                $productData[$attributes[$i]] = $values[$i];
            }

            $data[] = $productData;
        }

        return $data;
    }

    public function deleteProducts(string $idList): array
    {
        // Delete products from the products table
        $sqlProducts = "DELETE FROM products WHERE id IN ($idList)";
        $stmtProducts = $this->conn->prepare($sqlProducts);
        $stmtProducts->execute();
        $affectedProductsRows = $stmtProducts->rowCount();

        // Delete associated records from the product_attribute_associations table
        $sqlAssociations = "DELETE FROM product_attribute_associations WHERE product_id IN ($idList)";
        $stmtAssociations = $this->conn->prepare($sqlAssociations);
        $stmtAssociations->execute();
        $affectedAssociationsRows = $stmtAssociations->rowCount();

        return [
            "message" => "Products deleted successfully",
            "affected products rows" => $affectedProductsRows,
            "affected associations rows" => $affectedAssociationsRows
        ];
    }

    public function createProduct($data): string
    {
        $sql = "INSERT INTO products (sku, name, price, product_type_id)
                VALUES (:sku, :name, :price, :product_type_id)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":sku", $data['sku'], PDO::PARAM_STR);
        $stmt->bindValue(":name", $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(":price", $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(":product_type_id", $data['productTypeId'], PDO::PARAM_INT);

        $stmt->execute();

        $productId = $this->conn->lastInsertId();

        if ($productId) {
            foreach ($data['attributeIds'] as $attributeId => $attributeValue) {
                if (!$this->createProductAttributeAssociation($productId, $attributeId, $attributeValue)) {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Failed to create product attribute association"]);
                }
            }
        }

        return $productId;
    }

    private function createProductAttributeAssociation($productId, $attributeId, $value): bool
    {
        $sql = "INSERT INTO product_attribute_associations (product_id, attribute_id, value)
                VALUES (:product_id, :attribute_id, :value)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":product_id", $productId, PDO::PARAM_INT);
        $stmt->bindValue(":attribute_id", $attributeId, PDO::PARAM_INT);
        $stmt->bindValue(":value", $value, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function productExists(string $sku): bool
    {
        $sql = "SELECT COUNT(*) 
                FROM products 
                WHERE sku = :sku";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":sku", $sku, PDO::PARAM_STR);
        $stmt->execute();

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function lookupProductTypeId(string $productType): int
    {
        $sql = "SELECT id 
                FROM product_types 
                WHERE type = :type";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":type", $productType, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function lookupAttributeId(string $attributeName): int
    {
        $sql = "SELECT id 
                FROM product_attributes 
                WHERE attribute = :attribute";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":attribute", $attributeName, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getTypes(): array
    {
        $sql = "SELECT type 
                FROM product_types";

        $stmt = $this->conn->query($sql);

        $types = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $row['type'];
        }

        return $types;
    }

    public function getAttributes(): array
    {
        $sql = "SELECT attribute 
                FROM product_attributes";

        $stmt = $this->conn->query($sql);

        $attributes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attributes[] = $row['attribute'];
        }

        return $attributes;
    }
}
