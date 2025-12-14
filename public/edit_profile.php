<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$page_title = 'Редактирование профиля';
$page_styles = ['edit_profile.css'];
$page_scripts = ['edit_profile.js'];
require_once 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="edit-profile-page"> 
    <div class="container">
        <div class="profile-edit">
            <h1><i class="fas fa-user-edit"></i> Редактирование профиля</h1>
            
            <div class="edit-form">
                <form id="editProfileForm">
                    <div class="form-group">
                        <label for="full_name">ФИО</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="birth_date">Дата рождения</label>
                        <input type="date" id="birth_date" name="birth_date" 
                               value="<?php echo $user['birth_date']; ?>">
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="current_password">Текущий пароль (для подтверждения изменений)</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="password-toggle" data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="new_password">Новый пароль (оставьте пустым, если не меняете)</label>
                        <input type="password" id="new_password" name="new_password">
                        <button type="button" class="password-toggle" data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/user_cabinet.php" class="btn btn-outline">Отмена</a>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 

<?php require_once 'includes/footer.php'; ?>