<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * The Wordpress Class contains WP functions to retrieve Wordpress data from the DB.
 *
 *
 * @author     Masa Gumiro
 */
class Wordpress {

    private $CI;

    function Wordpress() {
        $this->CI = & get_instance();
    }

    /**
     * Get posts by tag
     *
     *
     * @param int $num Optional, default is 10. Number of posts to get.
     * @param varchar $tag Required The tag to get.
     * @return array List of posts.
     */
    function get_posts_by_tag($tag, $num=10) {
        // Set the limit clause, if we got a limit
        $num = (int) $num;
        if ($num) {
            $limit = " LIMIT $num";
        } else {
			$limit = "";	
		}
        $sql = "SELECT News.wp_posts.* FROM News.wp_posts, News.wp_term_relationships, News.wp_term_taxonomy, News.wp_terms WHERE News.wp_terms.name = '".$tag."' AND News.wp_terms.term_id = News.wp_term_taxonomy.term_id AND News.wp_term_taxonomy.term_taxonomy_id = News.wp_term_relationships.term_taxonomy_id AND News.wp_term_relationships.object_id = News.wp_posts.ID AND News.wp_posts.post_type = 'post' AND News.wp_posts.post_status IN ('publish') ORDER BY News.wp_posts.post_date DESC".$limit;
        $query = $this->CI->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        return $query->result();
    }

    /**
     * Retrieve number of recent posts.
     *
     *
     * @param int $num Optional, default is 10. Number of posts to get.
     * @return array List of posts.
     */
    function get_recent_posts($num = 10) {

        // Set the limit clause, if we got a limit
        $num = (int) $num;
        if ($num) {
            $limit = "LIMIT $num";
        }

        $sql = "SELECT * FROM News.wp_posts WHERE post_type = 'post' AND post_status IN ( 'publish' ) ORDER BY post_date DESC $limit";
        $query = $this->CI->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        return $query->result();
    }

    /**
     * Retrieve number of most popular posts.
     *
     *
     * @param int $num Optional, default is 10. Number of posts to get.
     * @return array List of posts.
     */
    function get_popular_posts($num = 10) {

        // Set the limit clause, if we got a limit
        $num = (int) $num;
        if ($num) {
            $limit = "LIMIT $num";
        }

        $sql = "SELECT * FROM News.wp_posts WHERE post_type = 'post' AND post_status IN ( 'publish' ) ORDER BY comment_count DESC $limit";
        $query = $this->CI->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        return $query->result();
    }

    /**
     * Retrieve a post tags.
     *
     *
      @param int $post_id required.
      @return array List of tags.
     */
    function get_post_tags($post_id) {
        $sql = "SELECT News.wp_terms.name, News.wp_terms.slug FROM News.wp_terms, News.wp_term_relationships WHERE wp_term_relationships.object_id = $post_id AND wp_term_relationships.term_taxonomy_id = wp_terms.term_id";
        $query = $this->CI->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }
        return $query->result();
    }

    /**
     * Retrieve a post specific meta information.
     *
     *
      @param int $post_id required.
      @param varchar $meta_key required.
      @return variable The meta value.
     */
    function get_meta_key_value($post_id, $meta_key) {
        $sql = "SELECT News.wp_postmeta.meta_value FROM News.wp_postmeta WHERE wp_postmeta.meta_key = '$meta_key' AND wp_postmeta.post_id = $post_id LIMIT 1";
        $query = $this->CI->db->query($sql);
        if ($query->num_rows() == 0) {
            return false;
        } else {
            $results = $query->result();
            foreach ($results as $result) {
                return $result->meta_value;
            }
        }
    }

}