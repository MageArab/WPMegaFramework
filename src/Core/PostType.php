<?php

namespace MageArab\MegaFramework\Core;

use MageArab\MegaFramework\Factories\NotifyFactory;
use MageArab\MegaFramework\Traits;

class PostType extends PluginObject
{
    use Traits\SmartObject;

    protected $description;
    protected $labels;
    protected $supports = array('title', 'editor');
    protected $taxonomies = array();
    protected $hierarchical = false;
    protected $isPublic = true;
    protected $showUI = true;
    protected $showInMenu = true;
    protected $menuPosition;
    protected $menuIcon;
    protected $capabilities = array(
        'edit_post' => Capability::EDIT_POSTS,
        'read_post' => Capability::READ_POSTS,
        'delete_post' => Capability::DELETE_POSTS,
        'delete_posts' => Capability::DELETE_POSTS,
        'edit_posts' => Capability::EDIT_POSTS,
        'edit_others_posts' => Capability::EDIT_OTHERS_POSTS,
        'publish_posts' => Capability::PUBLISH_POSTS,
        'read_private_posts' => Capability::READ_PRIVATE_POSTS,
    );
    protected $showInAdminBar = true;
    protected $showInNavMenus = true;
    protected $canExport = true;
    protected $excludeFromSearch = false;
    protected $publiclyQueryable = false;
    protected $hasArchive = true;
    public function __construct($name, $slug, $args)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->labels = $this->setLabels();
        add_action('init', array($this, 'registerPostType'));
    }

    private function setLabels()
    {
        $labels = array(
            'name' => _x($this->name, 'Post Type General Name', $this->textDomain),
            'singular_name' => _x('Post Type', 'Post Type Singular Name', $this->textDomain),
            'menu_name' => __($this->name, $this->textDomain),
            'name_admin_bar' => __('Post Type', $this->textDomain),
            'archives' => __('Item Archives', $this->textDomain),
            'parent_item_colon' => __('Parent Item:', $this->textDomain),
            'all_items' => __('All Items', $this->textDomain),
            'add_new_item' => __('Add New Item', $this->textDomain),
            'add_new' => __('Add New', $this->textDomain),
            'new_item' => __('New Item', $this->textDomain),
            'edit_item' => __('Edit Item', $this->textDomain),
            'update_item' => __('Update Item', $this->textDomain),
            'view_item' => __('View Item', $this->textDomain),
            'search_items' => __('Search Item', $this->textDomain),
            'not_found' => __('Not found', $this->textDomain),
            'not_found_in_trash' => __('Not found in Trash', $this->textDomain),
            'featured_image' => __('Featured Image', $this->textDomain),
            'set_featured_image' => __('Set featured image', $this->textDomain),
            'remove_featured_image' => __('Remove featured image', $this->textDomain),
            'use_featured_image' => __('Use as featured image', $this->textDomain),
            'uploaded_to_this_item' => __('Uploaded to this item', $this->textDomain),
            'items_list' => __($this->name . ' list', $this->textDomain),
            'items_list_navigation' => __($this->name . ' list navigation', $this->textDomain),
            'filter_items_list' => __('Filter ' . $this->name . ' list', $this->textDomain),
        );
        return $labels;
    }

    public function setDescription($description)
    {
        $this->description = __($description, $this->textDomain);
        return $this;
    }

    public function setSupports($supports)
    {
        if ($supports === true) {
            wp_die(__("The supports option must be an array or false", $this->textDomain));
        }
        $this->supports[] = $supports;
        return $this;
    }

    public function addTaxonomy($taxonomy)
    {
        if (!taxonomy_exists($taxonomy)) {
            NotifyFactory::error('No Registered Taxonomy With this slug' . $taxonomy);
        }
        $this->taxonomies[] = $taxonomy;
        return $this;
    }

    public function setMenuPosition($position)
    {
        $this->menuPosition = intval($position);
        return $this;
    }

    public function setMenuIcon($icon)
    {
        $this->menuIcon = $icon;
        return $this;
    }

    public function setCapabilities($capabilities)
    {
        foreach ($capabilities as $capability) {
            if (!Capability::isValid($capability)) {
                wp_die(__("{$capability} is not a valid WordPress capability."), $this->textDomain);
            }
        }
        $this->capabilities = $capabilities;
        return $this;
    }

    public function hasArchive($hasArchive)
    {
        $this->hasArchive = $hasArchive;
        return $this;
    }

    public function isPublic($public)
    {
        $this->isPublic = $public;
        return $this;
    }

    public function isHierarchical($hierarchical)
    {
        $this->hierarchical = $hierarchical;
        return $this;
    }

    public function registerPostType()
    {
        if (post_type_exists($this->getSlug())) {
            NotifyFactory::error(__($this->getName() . ' Post Type Already Exists', $this->textDomain));
        }
        $args = array(
            'label' => __('Post Type', $this->textDomain),
            'description' => $this->description,
            'labels' => $this->labels,
            'supports' => $this->supports,
            'taxonomies' => $this->taxonomies,
            'hierarchical' => $this->hierarchical,
            'public' => $this->isPublic,
            'show_ui' => $this->showUI,
            'show_in_menu' => $this->showInMenu,
            'menu_position' => $this->menuPosition,
            'menu_icon' => $this->menuIcon,
            'show_in_admin_bar' => $this->showInAdminBar,
            'show_in_nav_menus' => $this->showInNavMenus,
            'can_export' => $this->canExport,
            'exclude_from_search' => $this->excludeFromSearch,
            'publicly_queryable' => $this->publiclyQueryable,
            'has_archive' => $this->hasArchive,

        );
        register_post_type($this->getSlug(), $args);
    }
}