<?php
    /*
     * $Id: domainsettings.php 1439 2009-11-17 23:31:04Z dmorton $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
     *                   David Morton   <mortonda@dgrmm.net>
     * All rights reserved.
     *
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */

    require_once ("core.php");
    require_once ("authcheck.php");
    require_once ("display.php");
    require_once ("maia_db.php");
    $display_language = get_display_language($euid);
    require_once ("./locale/$display_language/display.php");
    require_once ("./locale/$display_language/db.php");
    require_once ("./locale/$display_language/domainsettings.php");

    require_once ("smarty.php");
    
    // A domain ID value *must* be supplied.
    if (isset($_GET["domain"])) {
       $domain_id = trim($_GET["domain"]);
    } else {
       header("Location: admindomains.php" . $sid);
    }

    // Only administrators with rights to this domain should be here.
    if (!is_admin_for_domain($uid, $domain_id)) {
       header("Location: index.php" . $sid);
       exit();
    }

    $sth = $dbh->prepare("SELECT virus_lover, " .
                     "spam_lover, " .
                     "banned_files_lover, " .
                     "bad_header_lover, " .
                     "bypass_virus_checks, " .
                     "bypass_spam_checks, " .
                     "bypass_banned_checks, " .
                     "bypass_header_checks, " .
                     "discard_viruses, " .
  	             "discard_spam, " .
  	             "discard_banned_files, " .
  	             "discard_bad_headers, " .
                     "spam_modifies_subj, " .
                     "spam_tag_level, " .
                     "spam_tag2_level, " .
                     "spam_kill_level, " .
                     "email, " .
                     "policy_id " .
              "FROM users, policy " .
              "WHERE users.policy_id = policy.id " .
              "AND users.maia_domain_id = ?");

    $system_default = false;
    $res = $sth->execute(array($domain_id));
    if (PEAR::isError($sth)) {
        die($sth->getMessage());
    }
    if ($row = $res->fetchRow()) {
        $address = $row["email"];
        if ($address == "@.") {
            $smarty->assign('address', $lang['text_system_default'] . " (@.)");
            $system_default = true;
        } else {
			$smarty->assign('address', $address);
        }
        $smarty->assign("system_default", $system_default);
        $smarty->assign('policy_id', $row["policy_id"]);
        $smarty->assign('level1', $row["spam_tag_level"]);
        $smarty->assign('level2', $row["spam_tag2_level"]);
        $smarty->assign('level3', $row["spam_kill_level"]);
        if ($row["virus_lover"] == 'Y') {
            $smarty->assign('v_l_checked', "checked");
            $smarty->assign('v_q_checked', "");
            $smarty->assign('v_d_checked', "");
        } else {
            $smarty->assign('v_l_checked', "");
            if ($row["discard_viruses"] == 'Y') {
		$smarty->assign('v_q_checked', "");
		$smarty->assign('v_d_checked', "checked");
	    } else {
		$smarty->assign('v_q_checked', "checked");
		$smarty->assign('v_d_checked', "");
	    }
        }
        if ($row["spam_lover"] == 'Y') {
            $smarty->assign('s_l_checked', "checked");
            $smarty->assign('s_q_checked', "");
            $smarty->assign('s_d_checked', "");
        } else {
            $smarty->assign('s_l_checked', "");
            if ($row["discard_spam"] == 'Y') {
		$smarty->assign('s_q_checked', "");
		$smarty->assign('s_d_checked', "checked");
	    } else {
		$smarty->assign('s_q_checked', "checked");
		$smarty->assign('s_d_checked', "");
	    }
        }
        if ($row["banned_files_lover"] == 'Y') {
            $smarty->assign('b_l_checked', "checked");
            $smarty->assign('b_q_checked', "");
            $smarty->assign('b_d_checked', "");
        } else {
            $smarty->assign('b_l_checked', "");
            if ($row["discard_banned_files"] == 'Y') {
		$smarty->assign('b_q_checked', "");
		$smarty->assign('b_d_checked', "checked");
	    } else {
		$smarty->assign('b_q_checked', "checked");
		$smarty->assign('b_d_checked', "");
	    }
        }
        if ($row["bad_header_lover"] == 'Y') {
            $smarty->assign('h_l_checked', "checked");
            $smarty->assign('h_q_checked', "");
            $smarty->assign('h_d_checked', "");
        } else {
            $smarty->assign('h_l_checked', "");
            if ($row["discard_bad_headers"] == 'Y') {
		$smarty->assign('h_q_checked', "");
		$smarty->assign('h_d_checked', "checked");
	    } else {
		$smarty->assign('h_q_checked', "checked");
		$smarty->assign('h_d_checked', "");
	    }
        }
        if ($row["bypass_virus_checks"] == 'Y') {
            $smarty->assign('bv_y_checked', "");
            $smarty->assign('bv_n_checked', "checked");
        } else {
            $smarty->assign('bv_y_checked', "checked");
            $smarty->assign('bv_n_checked', "");
        }
        if ($row["bypass_spam_checks"] == 'Y') {
            $smarty->assign('bs_y_checked', "");
            $smarty->assign('bs_n_checked', "checked");
        } else {
            $smarty->assign('bs_y_checked', "checked");
            $smarty->assign('bs_n_checked', "");
        }
        if ($row["bypass_banned_checks"] == 'Y') {
            $smarty->assign('bb_y_checked', "");
            $smarty->assign('bb_n_checked', "checked");
        } else {
            $smarty->assign('bb_y_checked', "checked");
            $smarty->assign('bb_n_checked', "");
        }
        if ($row["bypass_header_checks"] == 'Y') {
            $smarty->assign('bh_y_checked', "");
            $smarty->assign('bh_n_checked', "checked");
        } else {
            $smarty->assign('bh_y_checked', "checked");
            $smarty->assign('bh_n_checked', "");
        }
        if ($row["spam_modifies_subj"] == 'Y') {
            $smarty->assign('sms_y_checked', "checked");
            $smarty->assign('sms_n_checked', "");
        } else {
            $smarty->assign('sms_y_checked', "");
            $smarty->assign('sms_n_checked', "checked");
        }
 
    }
    $sth->free();
    
    //get list of themes
    $sth = $dbh->prepare("SELECT id, name FROM maia_themes");
    $res = $sth->execute();
    if (PEAR::isError($sth)) {
        die($sth->getMessage());
    }

    $themes = array();
    while ($row = $res->fetchrow()) {
       $themes[$row['id']] = $row['name'];
    }
    $smarty->assign("themes", $themes);
    $sth->free();
    
    $sth = $dbh->prepare("SELECT maia_users.discard_ham, maia_domains.enable_user_autocreation, maia_users.theme_id " .
  	                   "FROM maia_users, maia_domains " .
  	                   "WHERE maia_domains.domain = maia_users.user_name " .
  	                   "AND maia_domains.id = ?");
    $res = $sth->execute(array($domain_id));
    if ($row = $res->fetchrow()) {
        $smarty->assign('theme_id', $row["theme_id"]);
        if ($row["discard_ham"] == 'Y') {
            $smarty->assign('dh_y_checked', "");
            $smarty->assign('dh_n_checked', "checked");
        } else {
            $smarty->assign('dh_y_checked', "checked");
            $smarty->assign('dh_n_checked', "");
        }
        if (get_config_value("enable_user_autocreation") == 'Y') {
            $smarty->assign("system_enable_user_autocreation", true);
            if ($row["enable_user_autocreation"] == 'Y') {
                $smarty->assign('ua_y_checked', "checked");
                $smarty->assign('ua_n_checked', "");
            } else {
                $smarty->assign('ua_y_checked', "");
                $smarty->assign('ua_n_checked', "checked");
            }
        } else {
            $smarty->assign("system_enable_user_autocreation", false);
        }
    }
    $sth->free();
    
    $sth = $dbh->prepare("SELECT maia_users.user_name, maia_users.id " .
              "FROM maia_users, maia_domain_admins " .
              "WHERE maia_users.id = maia_domain_admins.admin_id " .
              "AND maia_domain_admins.domain_id = ? " .
              "ORDER BY maia_users.user_name ASC");
    $res = $sth->prepare(array($domain_id));
    if (PEAR::isError($sth)) {
        die($sth->getMessage());
    }
    $admins = array();
    if (($rowcount = $res->numrows()) > 0) {
        while ($row = $res->fetchrow()) {
            $admins[] = array(
                'id' => $row["id"],
                'name' => $row["user_name"],
                'var_name' => $row["user_name"]
            );
        }
    }
    $smarty->assign('admins', $admins);

    $sth->free();

    $sth = $dbh->prepare("SELECT maia_users.id " .
              "FROM maia_users, maia_domain_admins " .
              "WHERE maia_users.id = maia_domain_admins.admin_id " .
              "AND maia_domain_admins.domain_id = ?");
    $res = $sth->execute(array($domain_id));
    if (PEAR::isError($sth)) {
        die($sth->getMessage());
    }
    $id_list = "";
    while($row = $res->fetchRow()) {
        if (!empty($id_list)) {
            $id_list .= "," . $row["id"];
        } else {
            $id_list = $row["id"];
        }
    }
    $sth->free();

    $select = "SELECT user_name, id " .
              "FROM maia_users " .
              "WHERE user_level <> 'S' " .
              "AND user_name NOT like '@%' ";
    if (!empty($id_list)) {
        $select .= "AND id NOT IN (" . $id_list . ") ";
    }
    $select .= "ORDER BY user_name ASC";
    $sth->prepare($select)
    $res = $sth->execute();
    if (PEAR::isError($sth)) {
        die($sth->getMessage());
    }

    if ($res->numrows()) {
        $add_admins = array();
        while ($row = $res->fetchrow()) {
            $add_admins[] = array(
                'id' => $row["id"],
                'name' => $row["user_name"]
            );
        }
        $smarty->assign('add_admins', $add_admins);
    }
    $sth->free();
    $smarty->assign('domain_id', $domain_id);
    $smarty->display('domainsettings.tpl');
    
?>
