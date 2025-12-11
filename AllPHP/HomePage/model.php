<?php
require_once '../utils/function.php';

$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;

$categories = getAllCategories();

if (!empty($searchQuery)) {
    $conn = getDBConnection();
    $query = "SELECT m.*, c.CategoryName FROM Model m 
              LEFT JOIN Category c ON m.CategoryID = c.CategoryID 
              WHERE m.ModelName LIKE ? 
              ORDER BY m.ModelName ASC";
    $stmt = mysqli_prepare($conn, $query);
    $searchTerm = "%{$searchQuery}%";
    mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $models = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $models[] = $row;
    }
    mysqli_close($conn);
} else {
    $models = getAllModels($categoryFilter, 100, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - Models</title>
    <link rel="stylesheet" href="../assets/css/model.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <nav>
        <div class="logo">
            <a href="../index.php">
            <img src="../assets/image/Future.png" alt=""></a>
        </div>
        <ul class="nav-links">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php" class="active">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <?php if (isUserLoggedIn()): ?>
                <li><a href="userProfile.php">PROFILE</a></li>
                <li><a href="../Admin/logout.php">LOGOUT</a></li>
            <?php else: ?>
                <li><a href="login.php">LOGIN</a></li>
                <li><a href="signUp.php">SIGN UP</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <section class="models-header">
        <div class="search-filter">
            <form method="GET" action="model.php" class="filter-form">
                <div class="search-box">
                    <input type="text" 
                           name="search" 
                           placeholder="Search model by name..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="search-icon">
                        <img src="../assets/image/Search.png" alt="Search">
                    </button>
                </div>
                <div class="filter-box">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['CategoryID']; ?>" 
                                    <?php echo ($categoryFilter == $category['CategoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['CategoryName']); ?> 
                                (<?php echo $category['model_count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="filter-icon">▼</span>
                </div>
            </form>
        </div>
        <div class="header-text">
            <h1>EVERYONE IN FUTURE MANIFESTS THE ESSENCE OF AN ICON WHERE WE POSSESS VALUES AND QUALITIES REPRESENTING COMMUNITY'S ASPIRATIONS.</h1>
        </div>
    </section>
    
    <section class="models-section">
        <?php if (!empty($searchQuery) || $categoryFilter): ?>
            <div class="filter-info">
                <?php if (!empty($searchQuery)): ?>
                    <p>Showing results for: <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>
                <?php endif; ?>
                <?php if ($categoryFilter): ?>
                    <?php 
                    $selectedCategory = getCategoryById($categoryFilter);
                    if ($selectedCategory):
                    ?>
                        <p>Category: <strong><?php echo htmlspecialchars($selectedCategory['CategoryName']); ?></strong></p>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="model.php" class="clear-filters">Clear Filters</a>
            </div>
        <?php endif; ?>
        
        <div class="models-grid">
            <?php if (!empty($models)): ?>
                <?php foreach ($models as $model): ?>
                    <div class="model-card" data-model-id="<?php echo $model['ModelID']; ?>">
                        <div class="model-image">
                            <?php if (!empty($model['ModelImage'])): ?>
                                <img src="../uploads/models/<?php echo htmlspecialchars($model['ModelImage']); ?>" 
                                     alt="<?php echo htmlspecialchars($model['ModelName']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <span><?php echo strtoupper(substr($model['ModelName'], 0, 2)); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="model-overlay">
                                <div class="model-details">
                                    <?php if (!empty($model['Height'])): ?>
                                        <p><strong>Height:</strong> <?php echo $model['Height']; ?> cm</p>
                                    <?php endif; ?>
                                    <?php if (!empty($model['CategoryName'])): ?>
                                        <p><strong>Category:</strong> <?php echo htmlspecialchars($model['CategoryName']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <h3 class="model-name"><?php echo htmlspecialchars($model['ModelName']); ?></h3>
                        <?php if (!empty($model['CategoryName'])): ?>
                            <p class="model-category"><?php echo htmlspecialchars($model['CategoryName']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-models-message">
                    <h3>No models found</h3>
                    <?php if (!empty($searchQuery)): ?>
                        <p>No models match your search for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                    <?php elseif ($categoryFilter): ?>
                        <p>No models in this category yet.</p>
                    <?php else: ?>
                        <p>No models available at the moment.</p>
                    <?php endif; ?>
                    <a href="model.php" class="back-link">← Back to all models</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <footer>
        <div class="footer-logo">
           <img src="../assets/image/Future.png" alt="Future Models">
        </div>
        
        <ul class="footer-nav">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <li><a href="signUp.php">SIGN UP</a></li>
        </ul>

        <div class="footer-contact">
            info@futuremodels.id.com
        </div>

        <div class="social-links">
            <a href="#" aria-label="Instagram"><img src="../assets/image/mdi_instagram.png" alt="Instagram"></a>
            <a href="#" aria-label="YouTube"><img src="../assets/image/mdi_youtube.png" alt="YouTube"></a>
            <a href="#" aria-label="TikTok"><img src="../assets/image/ic_baseline-tiktok.png" alt="TikTok"></a>
        </div>

        <div class="copyright">
            Copyright © futuremodels.id 2025
        </div>
    </footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modelCards = document.querySelectorAll('.model-card');
        modelCards.forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
                    return;
                }
                
                const modelId = this.getAttribute('data-model-id');
                if (modelId) {
                    window.location.href = `modeldetail.php?id=${modelId}`;
                }
            });
        });
    });
</script>
</body>
</html>