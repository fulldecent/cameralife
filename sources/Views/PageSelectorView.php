<?php
namespace CameraLife\Views;

/**
 * PageSelector view
 *
 * @author    William Entriken<cameralife@phor.net>
 * @access    public
 * @copyright 2014 William Entriken
 */
class PageSelectorView extends View
{
    /**
     * The first item number shown
     *
     * @var    int
     * @access public
     */
    public $start;

    /**
     * The total number of items
     *
     * @var    int
     * @access public
     */
    public $total;

    /**
     * The number of items per page
     *
     * (default value: 20)
     *
     * @var    int
     * @access public
     */
    public $perPage = 20;

    /**
     * The base URL of all links
     *
     * (default value: null)
     *
     * @var    mixed
     * @access public
     */
    public $url = null;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        $start = $this->start;
        $total = $this->total;
        $perPage = $this->perPage;
        $url = $this->url;

        if ($total <= $perPage) {
            return; // Refuse to only show one page
        }
        echo '<ul class="pagination">';
        if ($start == -1) {
            echo "<span class='current'>Randomly showing <b>$perPage</b> of <b>$total</b></span> ";
            echo "<a class='nextprev' href=\"$url\">More &#187;</a>";
        } else {
            $first = max($start - 3 * $perPage, 0);
            $last = min($first + 6 * $perPage, intval(($total - 1) / $perPage) * $perPage);
            $first = max($last - 6 * $perPage, 0);
            if ($last == 0) {
                $last = 1;
            }

            if ($first != $start) {
                parse_str(parse_url($url, PHP_URL_QUERY), $query);
                $query['start'] = $start - $perPage;
                $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
                echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">&laquo;</a></li>";
            } else {
                echo "<li class=\"disabled\"><span>&laquo;</span></li>";
            }

            for ($i = $first; $i <= $last; $i += $perPage) {
                parse_str(parse_url($url, PHP_URL_QUERY), $query);
                $query['start'] = $i;
                $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
                if ($i == $start) {
                    echo "<li class=\"active\"><a href=\"" . htmlspecialchars(
                        $newURL
                    ) . "\">" . ($i / $perPage + 1) . "</a></li>";
                } else {
                    echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">" . ($i / $perPage + 1) . "</a></li>";
                }
            }

            if ($last != $start) {
                parse_str(parse_url($url, PHP_URL_QUERY), $query);
                $query['start'] = $start + $perPage;
                $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);

                echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">&raquo;</a></li>";
            } else {
                echo "<li class=\"disabled\"><span>&raquo;</span></li>";
            }
        }
        echo "</ul>\n";
    }
}
