<?php
/*
Plugin Name: ارسال تصادفی نظرات
Description: نظرات را از سه فایل TEXT وارد کنید و به طور تصادفی آنها را به پست ها اختصاص دهید.
Version: 1.4
Author: Pr-Mir
Author URI: https://github.com/Scary-technologies
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: comment-manager
Domain Path: /languages
*/

function import_random_comments_menu() {
    add_menu_page('ارسال تصادفی نظرات', 'ارسال تصادفی نظرات', 'manage_options', 'import-random-comments', 'import_random_comments_page');
}

add_action('admin_menu', 'import_random_comments_menu');

function import_random_comments_page() {
    if (isset($_POST['submit'])) {
        if (isset($_FILES['usernames_file']) && $_FILES['usernames_file']['error'] == 0 &&
            isset($_FILES['emails_file']) && $_FILES['emails_file']['error'] == 0 &&
            isset($_FILES['comments_file']) && $_FILES['comments_file']['error'] == 0 &&
            isset($_POST['num_comments']) && is_numeric($_POST['num_comments']) &&
            isset($_POST['category'])) {
            
            $usernames_file = $_FILES['usernames_file']['tmp_name'];
            $emails_file = $_FILES['emails_file']['tmp_name'];
            $comments_file = $_FILES['comments_file']['tmp_name'];
            $num_comments = intval($_POST['num_comments']);
            $category = intval($_POST['category']);
            
            import_comments_from_txt($usernames_file, $emails_file, $comments_file, $num_comments, $category);
        }
    }

    // Get all categories
    $categories = get_categories();
    ?>
    <div class="wrap">
        <h1>درون‌ریزی نظرات</h1>
        <form method="post" enctype="multipart/form-data" style="background: #fff; padding: 20px; border: 1px solid #ccc; box-shadow: 0 1px 1px rgba(0,0,0,.04); max-width: 600px; margin: 0 auto;">
            <div style="margin-bottom: 10px;">
                <label for="usernames_file" style="display: inline-block; width: 120px;">نام کابران:</label>
                <input type="file" name="usernames_file" id="usernames_file" required />
            </div>
            <div style="margin-bottom: 10px;">
                <label for="emails_file" style="display: inline-block; width: 120px;">ایمیل کاربران:</label>
                <input type="file" name="emails_file" id="emails_file" required />
            </div>
            <div style="margin-bottom: 10px;">
                <label for="comments_file" style="display: inline-block; width: 120px;">نظر کاربران:</label>
                <input type="file" name="comments_file" id="comments_file" required />
            </div>
            <div style="margin-bottom: 10px;">
                <label for="num_comments" style="display: inline-block; width: 120px;">تعداد نظرات:</label>
                <input type="number" name="num_comments" id="num_comments" required />
            </div>
            <div style="margin-bottom: 10px;">
                <label for="category" style="display: inline-block; width: 120px;">دسته‌بندی:</label>
                <select name="category" id="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="text-align: center;">
                <input type="submit" name="submit" value="درون‌ریزی نظرات" class="button button-primary" />
            </div>
        </form>
    </div>
    <?php
}

function import_comments_from_txt($usernames_file, $emails_file, $comments_file, $num_comments, $category) {
    $usernames = file($usernames_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = file($emails_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $comments = file($comments_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (empty($usernames) || empty($emails) || empty($comments)) {
        echo '<div class="error"><p>تمامی فایل‌ها باید شامل حداقل یک خط باشند.</p></div>';
        return;
    }

    $posts = get_posts([
        'numberposts' => -1,
        'category' => $category
    ]);
    if (empty($posts)) {
        echo '<div class="error"><p>هیچ پستی در این دسته‌بندی پیدا نشد.</p></div>';
        return;
    }

    for ($i = 0; $i < $num_comments; $i++) {
        $username = $usernames[array_rand($usernames)];
        $email = $emails[array_rand($emails)];
        $comment_text = $comments[array_rand($comments)];

        $random_post = $posts[array_rand($posts)];

        $comment_data = [
            'comment_post_ID' => $random_post->ID,
            'comment_author' => $username,
            'comment_author_email' => $email,
            'comment_content' => $comment_text,
            'comment_approved' => 1,
        ];

        wp_insert_comment($comment_data);
    }

    echo '<div class="updated"><p>نظرات با موفقیت درون‌ریزی شدند.</p></div>';
}

?>
