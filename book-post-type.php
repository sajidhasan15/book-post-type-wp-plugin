<?php
/**
 * Plugin Name: Book Post Type
 * Description: A custom post type for Books with custom fields for Author, Description, Shopping Link, and Published Date.
 * Version: 1.0
 * Author: Sajid Hasan
 */

// Step 1: Register the Custom Post Type
add_action( 'init', 'devblog_register_book_type' );

function devblog_register_book_type() {
    register_post_type( 'book', [
        'public'             => true,
        'show_in_rest'       => true,
        'capability_type'    => 'post',
        'has_archive'        => 'books',
        'menu_icon'          => 'dashicons-book',
        'supports'           => [ 'editor', 'excerpt', 'title', 'thumbnail' ], // 'thumbnail' enables Featured Image
        'rewrite'            => [ 'slug' => 'books' ],
        'labels'             => [
            'name'          => __( 'Books',        'devblog-plugin-templates' ),
            'singular_name' => __( 'Book',         'devblog-plugin-templates' ),
            'add_new'       => __( 'Add New Book', 'devblog-plugin-templates' )
        ]
    ] );

    // Flush permalinks after registering the post type
    flush_rewrite_rules();
}

// Step 2: Add Meta Boxes for Custom Fields
add_action( 'add_meta_boxes', 'devblog_add_book_meta_boxes' );

function devblog_add_book_meta_boxes() {
    add_meta_box(
        'book_details_meta_box',     // ID
        'Book Details',              // Title
        'devblog_display_book_meta_box', // Callback function
        'book',                      // Post type
        'normal',                    // Context
        'high'                       // Priority
    );
}

// Step 3: Display the Meta Box HTML
function devblog_display_book_meta_box( $post ) {
    // Retrieve current values for all custom fields
    $book_name = get_post_meta( $post->ID, 'book_name', true );
    $author_name = get_post_meta( $post->ID, 'author_name', true );
    $description = get_post_meta( $post->ID, 'description', true );
    $book_link = get_post_meta( $post->ID, 'book_link', true );
    $published_date = get_post_meta( $post->ID, 'published_date', true );
    ?>
    <div class="meta-box-field">
        <label for="book_name">Book Name:</label>
        <input type="text" id="book_name" name="book_name" value="<?php echo esc_attr( $book_name ); ?>" />
    </div>
    <div class="meta-box-field">
        <label for="author_name">Author Name:</label>
        <input type="text" id="author_name" name="author_name" value="<?php echo esc_attr( $author_name ); ?>" />
    </div>
    <div class="meta-box-field">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4"><?php echo esc_textarea( $description ); ?></textarea>
    </div>
    <div class="meta-box-field">
        <label for="book_link">Book Online Shopping Link:</label>
        <input type="url" id="book_link" name="book_link" value="<?php echo esc_url( $book_link ); ?>" />
    </div>
    <div class="meta-box-field">
        <label for="published_date">Book Published Date:</label>
        <input type="date" id="published_date" name="published_date" value="<?php echo esc_attr( $published_date ); ?>" />
    </div>
    <?php
}

// Step 4: Save Meta Box Data
add_action( 'save_post', 'devblog_save_book_meta_data' );

function devblog_save_book_meta_data( $post_id ) {
    // Check for autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Validate and save data
    if ( isset( $_POST['book_name'] ) ) {
        update_post_meta( $post_id, 'book_name', sanitize_text_field( $_POST['book_name'] ) );
    }

    if ( isset( $_POST['author_name'] ) ) {
        update_post_meta( $post_id, 'author_name', sanitize_text_field( $_POST['author_name'] ) );
    }

    if ( isset( $_POST['description'] ) ) {
        update_post_meta( $post_id, 'description', sanitize_textarea_field( $_POST['description'] ) );
    }

    if ( isset( $_POST['book_link'] ) ) {
        update_post_meta( $post_id, 'book_link', esc_url_raw( $_POST['book_link'] ) );
    }

    if ( isset( $_POST['published_date'] ) ) {
        update_post_meta( $post_id, 'published_date', sanitize_text_field( $_POST['published_date'] ) );
    }
}

// Step 5: Display the custom field values and Featured Image on the frontend
add_filter( 'the_content', 'devblog_display_book_details' );

function devblog_display_book_details( $content ) {
    if ( is_singular( 'book' ) ) {
        $book_name = get_post_meta( get_the_ID(), 'book_name', true );
        $author_name = get_post_meta( get_the_ID(), 'author_name', true );
        $description = get_post_meta( get_the_ID(), 'description', true );
        $book_link = get_post_meta( get_the_ID(), 'book_link', true );
        $published_date = get_post_meta( get_the_ID(), 'published_date', true );
        $featured_image = get_the_post_thumbnail( get_the_ID(), 'large' ); // Set the size to 'large' or any size you prefer

        // Structure the content with the cover photo at the top and all details below
        $additional_content = '<div class="book-details">';
        $additional_content .= '<div class="book-cover">' . $featured_image . '</div>';  // Display the featured image as the cover photo
        $additional_content .= '<div class="book-info">';  // Wrap the book info in a div
        $additional_content .= '<h2>' . esc_html( $book_name ) . '</h2>';
        $additional_content .= '<p><strong>Author:</strong> ' . esc_html( $author_name ) . '</p>';
        $additional_content .= '<p><strong>Description:</strong> ' . esc_html( $description ) . '</p>';
        $additional_content .= '<p><strong>Published Date:</strong> ' . esc_html( $published_date ) . '</p>';
        $additional_content .= '<p><a href="' . esc_url( $book_link ) . '">Buy Online</a></p>';
        $additional_content .= '</div>';  // Close the book info div
        $additional_content .= '</div>';  // Close the book details div

        return $content . $additional_content;
    }

    return $content;
}

// Step 6: Enqueue the CSS for admin and frontend
add_action( 'wp_enqueue_scripts', 'devblog_enqueue_book_styles' );
add_action( 'admin_enqueue_scripts', 'devblog_enqueue_book_styles' );

function devblog_enqueue_book_styles() {
    wp_enqueue_style( 'book-post-type-style', plugin_dir_url( __FILE__ ) . 'assets/css/book-style.css' );
}
