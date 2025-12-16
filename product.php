<?php
session_start();
include('config.php');

if (!isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Товар не найден";
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($product['name']) ?> - Интернет-магазин видеорегистраторов</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	
	<link rel="stylesheet" href="css/home.css">
 
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-12 mb-4">
                <a href="home.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Назад к каталогу
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="product-card bg-white">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 class="img-fluid product-image w-100" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="col-md-6 p-4 d-flex flex-column">
                            <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                            
                            <div class="price-tag mb-4">
                                <?= number_format($product['price'], 0, '', ' ') ?> ₽
                            </div>
                            
                            <div class="description mb-4">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </div>
                            
                            <form method="POST" action="home.php" class="mt-auto">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="quantity" class="col-form-label">Количество:</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="number" id="quantity" name="quantity" 
                                               class="form-control" value="1" min="1" style="width: 80px;">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                                            <i class="fas fa-shopping-cart me-2"></i>В корзину
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>