<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme UR Courses - Core renderer
 *
 * @package    theme_urcourses_default
 */

namespace theme_urcourses_default\output;

use moodle_url;
use context_course;

/**
 * Extending the core_renderer interface.
 *
 * @package    theme_urcourses_default
 */
class core_renderer extends \theme_boost_union\output\core_renderer {
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }

    public function get_compact_logosmall_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }

    /**
     * Renders the login form.
     *
     * This renderer function is copied and modified from /lib/classes/output/core_renderer.php
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);

        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string(
            $SITE->fullname,
            true,
            ['context' => context_course::instance(SITEID), "escape" => false]
        );
        $context->siteshortname = format_string(
            $SITE->shortname,
            true,
            ['context' => context_course::instance(SITEID), 'escape' => false]
        );
        $context->hasauthinstructions = !empty($CFG->auth_instructions);

        // Get the login page arrangement setting to differentiate based on this setting if needed.
        $loginpagearrangement = get_config('theme_boost_union', 'loginpagearrangement');

        // Shibboleth internal WAYF: If the Boost Union setting is enabled and if Shibboleth authentication is enabled.
        $context->showshibbolethembeddedwayfcode = false;
        $loginshibbolethinternalwayf = get_config('theme_boost_union', 'loginshibbolethinternalwayf');
        if (
            $loginshibbolethinternalwayf !== false &&
                $loginshibbolethinternalwayf !== THEME_BOOST_UNION_SETTING_SELECT_NO &&
                strpos($CFG->auth, 'shibboleth') !== false &&
                !empty($context->identityproviders)
        ) {
            // If we should replace the Shibboleth IdP button with the IdP selector from auth_shibboleth.
            if ($loginshibbolethinternalwayf === THEME_BOOST_UNION_SETTING_SHIBBOLETH_CONFIG) {
                // Require Shibboleth library.
                require_once($CFG->dirroot . '/auth/shibboleth/auth.php');

                // Get the Shibboleth authentication plugin config.
                get_auth_plugin('shibboleth');
                $shibconfig = get_config('auth_shibboleth');

                // Only show the internal WAYF if the user attribute for IdP selection and the organization selection
                // are configured in Shibboleth.
                if (!empty($shibconfig->user_attribute) && !empty($shibconfig->organization_selection)) {
                    // Get the list of IdPs from Shibboleth and check if there are any IdPs configured.
                    $idplist = get_idp_list($shibconfig->organization_selection);
                    if (!empty($idplist)) {
                        // Compose the WAYF data.
                        // This logic is copied and modified from /auth/shibboleth/login.php.
                        $selectedidp = '-';
                        if (isset($_COOKIE['_saml_idp'])) {
                            $idpcookie = generate_cookie_array($_COOKIE['_saml_idp']);
                            do {
                                $selectedidp = array_pop($idpcookie);
                            } while (!isset($idplist[$selectedidp]) && count($idpcookie) > 0);
                        }
                        $shibbidps = [];
                        foreach ($idplist as $value => $data) {
                            $name = reset($data);
                            $shibbidps[] = [
                                'name' => $name,
                                'value' => $value,
                                'selected' => $value === $selectedidp,
                            ];
                        }
                        $shibbolethloginurl = (new moodle_url('/auth/shibboleth/login.php'))->out(false);
                        $adminemail = get_admin()->email;
                        foreach ($context->identityproviders as $idx => $idp) {
                            $idpurl = $idp['url'] ?? '';
                            if (strpos($idpurl, '/auth/shibboleth/index.php') !== false) {
                                $context->identityproviders[$idx]['useinternalwayf'] = true;
                                $context->identityproviders[$idx]['shibbidps'] = $shibbidps;
                                $context->identityproviders[$idx]['shibbolethloginurl'] = $shibbolethloginurl;
                                $context->identityproviders[$idx]['adminemail'] = $adminemail;
                                $context->identityproviders[$idx]['wayfformid'] = 'login-shibboleth-wayf-' . $idx;
                            }
                        }
                    }
                }

                // Otherwise, if we should replace the Shibboleth IdP button with the configured JavaScript code.
            } else if ($loginshibbolethinternalwayf === THEME_BOOST_UNION_SETTING_SHIBBOLETH_CODE) {
                // Simply use the configured code.
                $loginshibbolethembeddedwayfcode = get_config('theme_boost_union', 'internalshibbolethwayfcode');
                if (!empty($loginshibbolethembeddedwayfcode)) {
                    $context->showshibbolethembeddedwayfcode = true;
                    $context->shibbolethembeddedwayfcode = format_text(
                        $loginshibbolethembeddedwayfcode,
                        FORMAT_HTML,
                        ['trusted' => true, 'noclean' => true, 'filter' => false]
                    );
                }
            }
        }

        // Compute show* flags for all four login types (theme setting + Moodle core).
        // Visibility is controlled in the template via these show* parameters.

        // Local login: theme setting only.
        $loginlocalloginsetting = get_config('theme_boost_union', 'loginlocalloginenable');
        $showlocalloginenabled = ($loginlocalloginsetting != false)
            ? $loginlocalloginsetting
            : THEME_BOOST_UNION_SETTING_SELECT_YES;
        $context->showlocallogin = ($showlocalloginenabled == THEME_BOOST_UNION_SETTING_SELECT_YES);

        // IDP login: theme setting AND core has identity providers.
        $loginidploginenablesetting = get_config('theme_boost_union', 'loginidploginenable');
        $showidploginenabled = ($loginidploginenablesetting != false)
            ? $loginidploginenablesetting
            : THEME_BOOST_UNION_SETTING_SELECT_YES;
        $context->showidplogin = ($showidploginenabled == THEME_BOOST_UNION_SETTING_SELECT_YES) &&
            !empty($context->hasidentityproviders) &&
            !empty($context->identityproviders);

        // Guest login: theme setting AND Moodle core guest login button enabled.
        $loginguestloginenablesetting = get_config('theme_boost_union', 'loginguestloginenable');
        $showguestloginenabled = ($loginguestloginenablesetting != false) ?
            $loginguestloginenablesetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
        $coreguestloginbutton = !empty(get_config('core', 'guestloginbutton'));
        $context->showguestlogin = ($showguestloginenabled == THEME_BOOST_UNION_SETTING_SELECT_YES) &&
            $coreguestloginbutton &&
            !empty($context->canloginasguest);

        // Self registration: theme setting AND Moodle core registerauth configured.
        $loginselfregistrationenablesetting = get_config('theme_boost_union', 'loginselfregistrationenable');
        $showselfregistrationenabled = ($loginselfregistrationenablesetting != false) ?
            $loginselfregistrationenablesetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
        $coreregisterauth = !empty(get_config('core', 'registerauth'));
        $context->showselfregistration = ($showselfregistrationenabled == THEME_BOOST_UNION_SETTING_SELECT_YES)
            && $coreregisterauth
            && !empty($context->cansignup);

        // Compute intro and instruction settings, but only when the corresponding login type is shown.

        // Local login.
        if ($context->showlocallogin) {
            $loginlocalshowintrosetting = get_config('theme_boost_union', 'loginlocalshowintro');
            $showlocalloginintro = ($loginlocalshowintrosetting != false) ?
                $loginlocalshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
            if ($showlocalloginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $context->showlocalloginintro = true;
                $loginlocalintrotext = get_config('theme_boost_union', 'loginlocalintrotext');
                if (!empty($loginlocalintrotext)) {
                    $context->localloginintrotext = format_string($loginlocalintrotext);
                }
            }
            $loginlocalshowinstructionsetting = get_config('theme_boost_union', 'loginlocalshowinstruction');
            $showlocallogininstruction = ($loginlocalshowinstructionsetting != false) ?
                $loginlocalshowinstructionsetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
            if ($showlocallogininstruction == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $loginlocalinstructions = get_config('theme_boost_union', 'loginlocalinstructioncontent');
                if (isset($loginlocalinstructions) && !empty($loginlocalinstructions)) {
                    $context->showlocallogininstruction = true;
                    $context->locallogininstructions = format_text($loginlocalinstructions, FORMAT_HTML);
                    $loginlocalinstructionposition = get_config('theme_boost_union', 'loginlocalinstructionposition');
                    $context->locallogininstructionposition = ($loginlocalinstructionposition === false) ?
                        THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN : $loginlocalinstructionposition;
                    $context->locallogininstructionsbetween =
                        ($context->locallogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN);
                    $context->locallogininstructionsbelow =
                        ($context->locallogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BELOW);
                }
            }
            // Button color.
            $loginlocalbuttoncolorsetting = get_config('theme_boost_union', 'loginlocalbuttoncolor');
            if ($loginlocalbuttoncolorsetting !== false) {
                $context->localloginbtnclass = 'btn-' . $loginlocalbuttoncolorsetting;
            } else {
                $context->localloginbtnclass = 'btn-' . THEME_BOOST_UNION_SETTING_BUTTONCOLOR_PRIMARYFILLED;
            }
            // Button size (Bootstrap's medium size does not have an own class and does not need to be added).
            $loginlocalbuttonsizesetting = get_config('theme_boost_union', 'loginlocalbuttonsize');
            if (
                $loginlocalbuttonsizesetting !== false &&
                    $loginlocalbuttonsizesetting !== THEME_BOOST_UNION_SETTING_BUTTONSIZE_MEDIUM
            ) {
                $context->localloginbtnclass .= ' btn-' . $loginlocalbuttonsizesetting;
            }
        }

        // IDP login.
        if ($context->showidplogin) {
            $loginidpshowintrosetting = get_config('theme_boost_union', 'loginidpshowintro');
            $showidploginintro = ($loginidpshowintrosetting != false) ?
                $loginidpshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
            if ($showidploginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $context->showidploginintro = true;
                $loginidpintrotext = get_config('theme_boost_union', 'loginidpintrotext');
                if (!empty($loginidpintrotext)) {
                    $context->idploginintrotext = format_string($loginidpintrotext);
                }
            }
            $loginidpshowinstructionsetting = get_config('theme_boost_union', 'loginidpshowinstruction');
            $showidplogininstruction = ($loginidpshowinstructionsetting != false) ?
                $loginidpshowinstructionsetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
            if ($showidplogininstruction == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $loginidpinstructions = get_config('theme_boost_union', 'loginidpinstructioncontent');
                if (isset($loginidpinstructions) && !empty($loginidpinstructions)) {
                    $context->showidplogininstruction = true;
                    $context->idplogininstructions = format_text($loginidpinstructions, FORMAT_HTML);
                    $loginidpinstructionposition = get_config('theme_boost_union', 'loginidpinstructionposition');
                    $context->idplogininstructionposition = ($loginidpinstructionposition === false) ?
                        THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN : $loginidpinstructionposition;
                    $context->idplogininstructionsbetween =
                        ($context->idplogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN);
                    $context->idplogininstructionsbelow =
                        ($context->idplogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BELOW);
                }
            }
            // Button color.
            $loginidpbuttoncolorsetting = get_config('theme_boost_union', 'loginidpbuttoncolor');
            if ($loginidpbuttoncolorsetting !== false) {
                $context->idploginbtnclass = 'btn-' . $loginidpbuttoncolorsetting;
            } else {
                $context->idploginbtnclass = 'btn-' . THEME_BOOST_UNION_SETTING_BUTTONCOLOR_MOODLELIGHTOUTLINE;
            }
            // Button size (Bootstrap's medium size does not have an own class and does not need to be added).
            $loginidpbuttonsizesetting = get_config('theme_boost_union', 'loginidpbuttonsize');
            if (
                $loginidpbuttonsizesetting !== false &&
                    $loginidpbuttonsizesetting !== THEME_BOOST_UNION_SETTING_BUTTONSIZE_MEDIUM
            ) {
                $context->idploginbtnclass .= ' btn-' . $loginidpbuttonsizesetting;
            }
        }

        // Guest login.
        if ($context->showguestlogin) {
            $loginguestshowintrosetting = get_config('theme_boost_union', 'loginguestshowintro');
            $showguestloginintro = ($loginguestshowintrosetting != false) ?
                $loginguestshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
            if ($showguestloginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $context->showguestloginintro = true;
                $loginguestintrotext = get_config('theme_boost_union', 'loginguestintrotext');
                if (!empty($loginguestintrotext)) {
                    $context->guestloginintrotext = format_string($loginguestintrotext);
                }
            }
            $loginguestshowinstructionsetting = get_config('theme_boost_union', 'loginguestshowinstruction');
            $showguestlogininstruction = ($loginguestshowinstructionsetting != false) ?
                $loginguestshowinstructionsetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
            if ($showguestlogininstruction == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $loginguestinstructions = get_config('theme_boost_union', 'loginguestinstructioncontent');
                if (isset($loginguestinstructions) && !empty($loginguestinstructions)) {
                    $context->showguestlogininstruction = true;
                    $context->guestlogininstructions = format_text($loginguestinstructions, FORMAT_HTML);
                    $loginguestinstructionposition = get_config('theme_boost_union', 'loginguestinstructionposition');
                    $context->guestlogininstructionposition = ($loginguestinstructionposition === false) ?
                        THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN : $loginguestinstructionposition;
                    $context->guestlogininstructionsbetween =
                        ($context->guestlogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN);
                    $context->guestlogininstructionsbelow =
                        ($context->guestlogininstructionposition === THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BELOW);
                }
            }
            // Button color.
            $loginguestbuttoncolorsetting = get_config('theme_boost_union', 'loginguestbuttoncolor');
            if ($loginguestbuttoncolorsetting !== false) {
                $context->guestloginbtnclass = 'btn-' . $loginguestbuttoncolorsetting;
            } else {
                $context->guestloginbtnclass = 'btn-' . THEME_BOOST_UNION_SETTING_BUTTONCOLOR_SECONDARYFILLED;
            }
            // Button size (Bootstrap's medium size does not have an own class and does not need to be added).
            $loginguestbuttonsizesetting = get_config('theme_boost_union', 'loginguestbuttonsize');
            if (
                $loginguestbuttonsizesetting !== false &&
                    $loginguestbuttonsizesetting !== THEME_BOOST_UNION_SETTING_BUTTONSIZE_MEDIUM
            ) {
                $context->guestloginbtnclass .= ' btn-' . $loginguestbuttonsizesetting;
            }
        }

        // Self registration.
        if ($context->showselfregistration) {
            $loginselfregistrationshowintrosetting = get_config('theme_boost_union', 'loginselfregistrationshowintro');
            $showselfregistrationloginintro = ($loginselfregistrationshowintrosetting != false) ?
                $loginselfregistrationshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
            if ($showselfregistrationloginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $context->showselfregistrationloginintro = true;
                $loginselfregistrationintrotext = get_config('theme_boost_union', 'loginselfregistrationintrotext');
                if (!empty($loginselfregistrationintrotext)) {
                    $context->selfregistrationloginintrotext = format_string($loginselfregistrationintrotext);
                }
            }
            $loginselfregistrationshowinstructionsetting = get_config('theme_boost_union', 'loginselfregistrationshowinstruction');
            $showselfregistrationlogininstruction = ($loginselfregistrationshowinstructionsetting != false) ?
                $loginselfregistrationshowinstructionsetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
            if ($showselfregistrationlogininstruction == THEME_BOOST_UNION_SETTING_SELECT_YES) {
                $loginselfregistrationinstructions = get_config('theme_boost_union', 'loginselfregistrationinstructioncontent');
                if (isset($loginselfregistrationinstructions) && !empty($loginselfregistrationinstructions)) {
                    $context->showselfregistrationlogininstruction = true;
                    $context->selfregistrationlogininstructions = format_text($loginselfregistrationinstructions, FORMAT_HTML);
                    $loginselfregistrationinstructionposition =
                            get_config('theme_boost_union', 'loginselfregistrationinstructionposition');
                    $context->selfregistrationlogininstructionposition = ($loginselfregistrationinstructionposition === false) ?
                        THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN : $loginselfregistrationinstructionposition;
                    $context->selfregistrationlogininstructionsbetween =
                        ($context->selfregistrationlogininstructionposition ===
                            THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BETWEEN);
                    $context->selfregistrationlogininstructionsbelow =
                        ($context->selfregistrationlogininstructionposition ===
                            THEME_BOOST_UNION_SETTING_LOGININSTRUCTIONPOSITION_BELOW);
                }
            }
            // Button color.
            $loginselfregistrationbuttoncolorsetting = get_config('theme_boost_union', 'loginselfregistrationbuttoncolor');
            if ($loginselfregistrationbuttoncolorsetting !== false) {
                $context->selfregistrationloginbtnclass = 'btn-' . $loginselfregistrationbuttoncolorsetting;
            } else {
                $context->selfregistrationloginbtnclass = 'btn-' . THEME_BOOST_UNION_SETTING_BUTTONCOLOR_SECONDARYFILLED;
            }
            // Button size (Bootstrap's medium size does not have an own class and does not need to be added).
            $loginselfregistrationbuttonsizesetting = get_config('theme_boost_union', 'loginselfregistrationbuttonsize');
            if (
                $loginselfregistrationbuttonsizesetting !== false &&
                    $loginselfregistrationbuttonsizesetting !== THEME_BOOST_UNION_SETTING_BUTTONSIZE_MEDIUM
            ) {
                $context->selfregistrationloginbtnclass .= ' btn-' . $loginselfregistrationbuttonsizesetting;
            }
        }

        // Get and use login form layout setting.
        $loginlayoutsetting = get_config('theme_boost_union', 'loginlayout');
        $loginlayout = ($loginlayoutsetting != false) ? $loginlayoutsetting : THEME_BOOST_UNION_SETTING_LOGINLAYOUT_VERTICAL;
        $context->loginlayout = $loginlayout;

        // Set template marker for each layout type.
        $context->loginlayoutaccordion = ($loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_ACCORDION) ? true : false;
        $context->loginlayouttabs = ($loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_TABS) ? true : false;
        $context->loginlayoutvertical = ($loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_VERTICAL) ? true : false;

        $loginidpsplitsetting = get_config('theme_boost_union', 'loginidpsplit');
        $separateidppertab = (($loginidpsplitsetting !== false)
            ? $loginidpsplitsetting : THEME_BOOST_UNION_SETTING_SELECT_NO) === THEME_BOOST_UNION_SETTING_SELECT_YES;
        // Create sorted login methods array.
        // This ensures the DOM order matches the visual order, so CSS :first-of-type and :last-of-type work correctly.
        // Note: The template uses the same loop structure for all layouts, with conditionals for tabs vs vertical/accordion.
        $loginmethods = [];

        // Local login.
        if ($context->showlocallogin) {
            $order = get_config('theme_boost_union', 'loginorderlocal');
            if ($order === false) {
                $order = 1; // Default order.
            }
            $loginmethods[] = (object)[
                'id' => 'login-method-local',
                'name' => 'local',
                'order' => $order,
                'type' => 'local',
                'islocal' => true,
                'isidp' => false,
                'isfirsttimesignup' => false,
                'isguest' => false,
                'isfirst' => false,
            ];
        }

        // IDP login.
        if ($context->showidplogin) {
            $order = get_config('theme_boost_union', 'loginorderidp');
            if ($order === false) {
                $order = 2; // Default order.
            }
            // If the setting to separate IDPs per tab is enabled and if there are identity providers.
            if ($separateidppertab && !empty($context->identityproviders)) {
                // Create a separate login method for each IDP, so they can be rendered in separate tabs.
                // Preserve the original order of the IDPs as provided by Moodle core.
                $providers = array_values($context->identityproviders);
                foreach ($providers as $idx => $idp) {
                    $loginmethods[] = (object)[
                        'id' => 'login-method-idp-' . $idx,
                        'name' => 'idp',
                        'order' => $order,
                        'type' => 'idp',
                        'islocal' => false,
                        'isidp' => true,
                        'isfirsttimesignup' => false,
                        'isguest' => false,
                        'isfirst' => false,
                        'idpsplit' => true,
                        'idpsplitfirst' => ($idx === 0),
                        'identityproviders' => [$idp],
                        'idpidx' => $idx,
                    ];
                }

                // Otherwise, create a single login method for all IDPs,
                // so they can be rendered in a single tab or an accordion pane.
            } else {
                $loginmethods[] = (object)[
                    'id' => 'login-method-idp',
                    'name' => 'idp',
                    'order' => $order,
                    'type' => 'idp',
                    'islocal' => false,
                    'isidp' => true,
                    'isfirsttimesignup' => false,
                    'isguest' => false,
                    'isfirst' => false,
                ];
            }
        }

        // Self registration.
        if ($context->showselfregistration) {
            $order = get_config('theme_boost_union', 'loginorderfirsttimesignup');
            if ($order === false) {
                $order = 3; // Default order.
            }
            $loginmethods[] = (object)[
                'id' => 'login-method-firsttimesignup',
                'name' => 'firsttimesignup',
                'order' => $order,
                'type' => 'firsttimesignup',
                'islocal' => false,
                'isidp' => false,
                'isfirsttimesignup' => true,
                'isguest' => false,
                'isfirst' => false,
            ];
        }

        // Guest login.
        if ($context->showguestlogin) {
            $order = get_config('theme_boost_union', 'loginorderguest');
            if ($order === false) {
                $order = 4; // Default order.
            }
            $loginmethods[] = (object)[
                'id' => 'login-method-guest',
                'name' => 'guest',
                'order' => $order,
                'type' => 'guest',
                'islocal' => false,
                'isidp' => false,
                'isfirsttimesignup' => false,
                'isguest' => true,
                'isfirst' => false,
            ];
        }

        // Sort login methods by order setting.
        usort($loginmethods, function ($a, $b) {
            // Sort by order value first.
            $orderby = $a->order <=> $b->order;

            // If order values are different, use that for sorting.
            if ($orderby !== 0) {
                return $orderby;
            }

            // Otherwise, if order values are equal, sort by identity-provider order.
            // This covers as well the case when the admin configured the same order value for multiple login methods
            // as the spaceship operator also returns 0 when both operands are null/undefined
            // (which is the case for non-IDP methods that don't have an idpidx).
            return ($a->idpidx ?? 0) <=> ($b->idpidx ?? 0);
        });

        // Mark the first method in the sorted array.
        if (!empty($loginmethods)) {
            $loginmethods[0]->isfirst = true;
        }

        // Set login method labels:
        // - For IDP login methods with IDP split enabled, use the name of the first (or only) IDP as the label.
        // - For all other methods, use the corresponding tab label setting if tabs or accordion layout is enabled.
        // Otherwise use no label.

        // Determine if tab labels should be used based on the layout type.
        $usetablabels = $loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_TABS
            || $loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_ACCORDION;

        // Prepare an array of login method keys and their corresponding tab label config names and default strings.
        // If tab labels are not used for the current layout, this will be an empty array and the loop below will be skipped.
        $logintablabelconfigs = $usetablabels ? [
            'local' => [
                'config' => 'loginlocalloginlabel',
                'default' => 'loginlocalloginlabelsetting_default',
            ],
            'idp' => [
                'config' => 'loginidploginlabel',
                'default' => 'loginidploginlabelsetting_default',
            ],
            'firsttimesignup' => [
                'config' => 'loginselfregistrationloginlabel',
                'default' => 'loginselfregistrationloginlabelsetting_default',
            ],
            'guest' => [
                'config' => 'loginguestloginlabel',
                'default' => 'loginguestloginlabelsetting_default',
            ],
        ] : [];

        // Iterate over the login methods and set the label for each method based on the rules described above.
        foreach ($loginmethods as $method) {
            // For IDP login methods with IDP split enabled, use the name of the first (or only) IDP as the label.
            if (!empty($method->idpsplit)) {
                $idp = $method->identityproviders[0];
                $rawname = is_array($idp) ? ($idp['name'] ?? '') : ($idp->name ?? '');
                $method->label = format_string($rawname);
                continue;
            }

            // For all other methods, if tab labels are used for the current layout, use the corresponding tab label setting.
            // Otherwise use no label.
            if (!$usetablabels) {
                continue;
            }
            $labelconfig = $logintablabelconfigs[$method->name] ?? null;
            if ($labelconfig !== null) {
                $label = format_string(get_config('theme_boost_union', $labelconfig['config']));
                if ($label === false || $label === '') {
                    $label = format_string(get_string($labelconfig['default'], 'theme_boost_union'));
                }
            } else {
                $label = '';
            }
            $method->label = $label;
        }

        // Determine the active/primary login method.
        $primarylogin = get_config('theme_boost_union', 'primarylogin');
        if ($primarylogin === false) {
            $primarylogin = 'none';
        }
        // Set active method based on layout type.
        // For tabs: primarylogin match, or first if primarylogin is 'none'.
        // For accordion: primarylogin match only (no default to first).
        // For vertical: no active flags.
        foreach ($loginmethods as $method) {
            if ($loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_TABS) {
                // Tabs: Default to first method when primarylogin is 'none'.
                // If IDP split is enabled, also consider the idpsplitfirst flag for IDP methods.
                if (!empty($method->idpsplit)) {
                    $method->active = ($primarylogin === 'none' && $method->isfirst) ||
                        ($primarylogin === 'idp' && !empty($method->idpsplitfirst));
                } else {
                    $method->active = ($primarylogin === $method->name) || ($primarylogin === 'none' && $method->isfirst);
                }
            } else if ($loginlayout == THEME_BOOST_UNION_SETTING_LOGINLAYOUT_ACCORDION) {
                // Accordion: Only set active if matched, no default to first.
                // If IDP split is enabled, also consider the idpsplitfirst flag for IDP methods.
                if (!empty($method->idpsplit)) {
                    $method->active = ($primarylogin === 'idp' && !empty($method->idpsplitfirst));
                } else {
                    $method->active = ($primarylogin === $method->name);
                }
            } else {
                // Vertical layout: no active flags.
                $method->active = false;
            }
        }

        // Determine divider output for each login method in vertical layout.
        foreach ($loginmethods as $method) {
            $dividertypesetting = get_config('theme_boost_union', 'login' . $method->name . 'dividertype');
            $dividertype = ($dividertypesetting !== false)
                ? $dividertypesetting
                : THEME_BOOST_UNION_SETTING_LOGINDIVIDERTYPE_LINE;
            $method->showdividermargin = (!$method->isfirst &&
                $dividertype === THEME_BOOST_UNION_SETTING_LOGINDIVIDERTYPE_MARGIN);
            $method->showdividerline = (!$method->isfirst &&
                $dividertype === THEME_BOOST_UNION_SETTING_LOGINDIVIDERTYPE_LINE);
            $method->showdividerlinewithor = (!$method->isfirst &&
                $dividertype === THEME_BOOST_UNION_SETTING_LOGINDIVIDERTYPE_LINEWITHOR);
        }

        // Add the loginmethods to the template context.
        $context->loginmethods = $loginmethods;

        // Add global login instructions.
        $logininstructionsabove = get_config('theme_boost_union', 'logininstructionsabove');
        if (!empty($logininstructionsabove)) {
            $context->logininstructionsabove = format_text($logininstructionsabove, FORMAT_HTML);
        }
        $logininstructionsbelow = get_config('theme_boost_union', 'logininstructionsbelow');
        if (!empty($logininstructionsbelow)) {
            $context->logininstructionsbelow = format_text($logininstructionsbelow, FORMAT_HTML);
        }
        // Note: both settings are also read in layout/login.php to render the same content
        // in the left side panel on large screens (upper and lower part respectively).
        $logininstructionssideupper = get_config('theme_boost_union', 'logininstructionssideupper');
        if (!empty($logininstructionssideupper)) {
            $context->logininstructionssideupper = format_text($logininstructionssideupper, FORMAT_HTML);
        }
        $logininstructionssidelower = get_config('theme_boost_union', 'logininstructionssidelower');
        if (!empty($logininstructionssidelower)) {
            $context->logininstructionssidelower = format_text($logininstructionssidelower, FORMAT_HTML);
        }

        // Add login logo extra classes for alignment and margin bottom.
        $loginlogoclasses = [];
        // Alignment: map setting value to Bootstrap text-alignment class.
        $loginlogoalignment = get_config('theme_boost_union', 'loginlogoalignment');
        if (!empty($loginlogoalignment)) {
            switch ($loginlogoalignment) {
                case THEME_BOOST_UNION_SETTING_HORIZONTALALIGNMENT_LEFT:
                    $loginlogoalignment = '-start';
                    break;
                case THEME_BOOST_UNION_SETTING_HORIZONTALALIGNMENT_RIGHT:
                    $loginlogoalignment = '-end';
                    break;
                case THEME_BOOST_UNION_SETTING_HORIZONTALALIGNMENT_CENTER:
                default:
                    $loginlogoalignment = '-center';
                    break;
            }
            $loginlogoclasses[] = 'justify-content' . $loginlogoalignment;
        }
        // Margin bottom: map setting value (0-5) to Bootstrap mb-* class.
        $loginlogomarginbottom = get_config('theme_boost_union', 'loginlogomarginbottom');
        if (isset($loginlogomarginbottom)) {
            $loginlogoclasses[] = 'mb-' . $loginlogomarginbottom;
        }
        // Compose all classes into a single string and add to context.
        if (!empty($loginlogoclasses)) {
            $context->loginlogoclasses = implode(' ', $loginlogoclasses);
        }

        // Add login page brand context variables.
        $loginpagebrandsetting = get_config('theme_boost_union', 'loginpagebrand');
        $loginpagebrand = ($loginpagebrandsetting !== false)
            ? $loginpagebrandsetting : THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADINGTAGLINE;
        $loginbrandlogooptionvalues = [
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADINGTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADING,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOTAGLINE,
        ];
        $loginbrandheadingoptionvalues = [
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADINGTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADING,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_HEADINGTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_HEADING,
        ];
        $loginbrandtaglineoptionvalues = [
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOHEADINGTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_LOGOTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_HEADINGTAGLINE,
            THEME_BOOST_UNION_SETTING_LOGINPAGEBRAND_TAGLINE,
        ];
        $context->loginbrandshowlogo = in_array($loginpagebrand, $loginbrandlogooptionvalues);
        $context->loginbrandshowheading = in_array($loginpagebrand, $loginbrandheadingoptionvalues);
        $context->loginbrandshowtagline = in_array($loginpagebrand, $loginbrandtaglineoptionvalues);
        // Compute common heading and tagline label assets.
        $showwelcomeback = !empty(get_moodle_cookie()) ||
            (!empty($context->error) && $context->error === get_string('sessionerroruser', 'error'));
        // Compute heading text.
        $loginpageheadingsetting = get_config('theme_boost_union', 'loginpageheading');
        $loginpageheading = ($loginpageheadingsetting !== false)
            ? $loginpageheadingsetting : THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_LOGINTOFULLNAME;
        switch ($loginpageheading) {
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_LOGINTOSHORTNAME:
                $context->loginheadingtext = get_string('loginto', 'core', $context->siteshortname);
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMETOFULLNAME:
                $context->loginheadingtext = get_string('loginpagelabel_welcometo', 'theme_boost_union', $context->sitename);
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMETOSHORTNAME:
                $context->loginheadingtext = get_string('loginpagelabel_welcometo', 'theme_boost_union', $context->siteshortname);
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_FULLNAME:
                $context->loginheadingtext = $context->sitename;
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_SHORTNAME:
                $context->loginheadingtext = $context->siteshortname;
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOME:
                $context->loginheadingtext = get_string('loginpagelabel_welcome', 'theme_boost_union');
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMEBACK:
                $context->loginheadingtext = $showwelcomeback
                    ? get_string('loginpagelabel_welcomeback', 'theme_boost_union')
                    : get_string('loginpagelabel_welcome', 'theme_boost_union');
                break;
            case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_LOGINTOFULLNAME:
            default:
                $context->loginheadingtext = get_string('loginto', 'core', $context->sitename);
                break;
        }
        // Compute tagline text (only when tagline is shown).
        if ($context->loginbrandshowtagline) {
            $loginpagetaglinesetting = get_config('theme_boost_union', 'loginpagetagline');
            $loginpagetagline = ($loginpagetaglinesetting !== false)
                ? $loginpagetaglinesetting : THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOME;
            switch ($loginpagetagline) {
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_LOGINTOSHORTNAME:
                    $context->logintaglinetext = get_string('loginto', 'core', $context->siteshortname);
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMETOFULLNAME:
                    $context->logintaglinetext = get_string('loginpagelabel_welcometo', 'theme_boost_union', $context->sitename);
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMETOSHORTNAME:
                    $context->logintaglinetext =
                            get_string('loginpagelabel_welcometo', 'theme_boost_union', $context->siteshortname);
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_FULLNAME:
                    $context->logintaglinetext = $context->sitename;
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_SHORTNAME:
                    $context->logintaglinetext = $context->siteshortname;
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_LOGINTOFULLNAME:
                    $context->logintaglinetext = get_string('loginto', 'core', $context->sitename);
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOMEBACK:
                    $context->logintaglinetext = $showwelcomeback
                        ? get_string('loginpagelabel_welcomeback', 'theme_boost_union')
                        : get_string('loginpagelabel_welcome', 'theme_boost_union');
                    break;
                case THEME_BOOST_UNION_SETTING_LOGINPAGELABEL_WELCOME:
                default:
                    $context->logintaglinetext = get_string('loginpagelabel_welcome', 'theme_boost_union');
                    break;
            }
        }

        // If we are on MWP.
        if (\theme_boost_union\local\mwp::extension_present() == true) {
            // Call the BU MWP class method only if the class and method exist.
            if (
                class_exists('\\local_boost_union_mwp\\local\\layouts') &&
                    method_exists('\\local_boost_union_mwp\\local\\layouts', 'postprocess_login_templatecontext')
            ) {
                // Post-process the templatecontext array.
                $context = \local_boost_union_mwp\local\layouts::postprocess_login_templatecontext($context);
            }
        }

        // Add JS if the side-by-site arrangement is not active, if the tabs layout is active
        // and enhanced tabs layout behaviour is enabled.
        $loginenhancedtabslayout = get_config('theme_boost_union', 'loginenhancedtabslayout');
        if (
            $loginpagearrangement != THEME_BOOST_UNION_SETTING_LOGINARRANGEMENT_SIDEBYSIDE &&
            $context->loginlayouttabs && $loginenhancedtabslayout == THEME_BOOST_UNION_SETTING_SELECT_YES
        ) {
            $this->page->requires->js_call_amd('theme_boost_union/logintabs', 'init');
        }

        // Render the login form template with the context.
        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Wrapper for header elements.
     *
     * This renderer function is copied and modified from /lib/classes/output/core_renderer.php
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        $pagetype = $this->page->pagetype;
        $homepage = get_home_page();
        $homepagetype = null;
        // Add a special case since /my/courses is a part of the /my subsystem.
        if ($homepage == HOMEPAGE_MY || $homepage == HOMEPAGE_MYCOURSES) {
            $homepagetype = 'my-index';
        } else if ($homepage == HOMEPAGE_SITE) {
            $homepagetype = 'site-index';
        }
        if (
            $this->page->include_region_main_settings_in_header_actions() &&
                !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(\html_writer::div(
                $this->region_main_settings_menu(),
                'd-print-none',
                ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new \stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        // Add the course header image for rendering.
        if ($this->page->pagelayout == 'course' && (get_config('theme_boost_union', 'courseheaderimageenabled')
                        == THEME_BOOST_UNION_SETTING_SELECT_YES)) {
            // If course header images are activated, we get the course header image url
            // (which might be the fallback image depending on the course settings and theme settings).
            $header->courseheaderimageurl = theme_urcourses_default_get_course_header_image_url();
            // Additionally, get the course header image height.
            $header->courseheaderimageheight = get_config('theme_boost_union', 'courseheaderimageheight');
            // Additionally, get the course header image position.
            $header->courseheaderimageposition = get_config('theme_boost_union', 'courseheaderimageposition');
            // Additionally, get the template context attributes for the course header image layout.
            $courseheaderimagelayout = get_config('theme_boost_union', 'courseheaderimagelayout');
            switch($courseheaderimagelayout) {
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_HEADINGABOVE:
                    $header->courseheaderimagelayoutheadingabove = true;
                    $header->courseheaderimagelayoutstackedclass = '';
                    break;
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_STACKEDDARK:
                    $header->courseheaderimagelayoutheadingabove = false;
                    $header->courseheaderimagelayoutstackedclass = 'dark';
                    break;
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_STACKEDLIGHT:
                    $header->courseheaderimagelayoutheadingabove = false;
                    $header->courseheaderimagelayoutstackedclass = 'light';
                    break;
            }
        }

        if (!empty($pagetype) && !empty($homepagetype) && $pagetype == $homepagetype) {
            $header->welcomemessage = \core\user::welcome_message();
        }
        return $this->render_from_template('core/full_header', $header);
    }

    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string {
        global $COURSE, $DB, $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 't/message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $isrequested = \core_message\api::get_contact_requests_between_users($USER->id, $user->id);
                        $contacturlaction = '';
                        $linkattributes = \core_message\helper::togglecontact_link_params(
                            $user,
                            $iscontact,
                            true,
                            !empty($isrequested),
                        );
                        // If the user is not a contact.
                        if (!$iscontact) {
                            if ($isrequested) {
                                // We just need the first request.
                                $requests = array_shift($isrequested);
                                if ($requests->userid == $USER->id) {
                                    // If the user has requested to be a contact.
                                    $contacttitle = 'contactrequestsent';
                                } else {
                                    // If the user has been requested to be a contact.
                                    $contacttitle = 'waitingforcontactaccept';
                                }
                                $linkattributes = array_merge($linkattributes, [
                                    'class' => 'disabled',
                                    'tabindex' => '-1',
                                ]);
                            } else {
                                // If the user is not a contact and has not requested to be a contact.
                                $contacttitle = 'addtoyourcontacts';
                                $contacturlaction = 'addcontact';
                            }
                            $contactimage = 't/addcontact';
                        } else {
                            // If the user is a contact.
                            $contacttitle = 'removefromyourcontacts';
                            $contacturlaction = 'removecontact';
                            $contactimage = 't/removecontact';
                        }
                        $userbuttons['togglecontact'] = array(
                                'buttontype' => 'togglecontact',
                                'title' => get_string($contacttitle, 'message'),
                                'url' => new moodle_url('/message/index.php', array(
                                        'user1' => $USER->id,
                                        'user2' => $user->id,
                                        $contacturlaction => $user->id,
                                        'sesskey' => sesskey())
                                ),
                                'image' => $contactimage,
                                'linkattributes' => $linkattributes,
                                'page' => $this->page
                            );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                $heading = format_string($this->page->course->fullname, true, ['context' => $context]);
            } else {
                $heading = $this->page->cm->get_formatted_name();
                $iconurl = $this->page->cm->get_icon_url();
                $iconclass = $iconurl->get_param('filtericon') ? '' : 'nofilter';
                $iconattrs = [
                    'class' => "icon activityicon $iconclass",
                    'aria-hidden' => 'true'
                ];
                $imagedata = \html_writer::img($iconurl->out(false), '', $iconattrs);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer icon-size-6';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $isbranded = component_callback('mod_' . $this->page->activityname, 'is_branded', [], false);
                $imagedata = \html_writer::tag('div', $imagedata, ['class' => $purposeclass . ($isbranded ? ' isbranded' : '')]);
                if (!empty($USER->editing)) {
                    $prefix = get_string('modulename', $this->page->activityname);
                }
            }
        }

        $instructors = [];
        if ($context->contextlevel == CONTEXT_COURSE) {
            $instructors = $this->get_instructors($COURSE->id);
        }

        $contextheader = new \theme_urcourses_default\output\context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix, $instructors);
        return $this->render($contextheader);
    }

    private function get_instructors($courseid) {
        global $CFG, $DB;
        if ($courseid == SITEID) {
            return '';
        }

        $users = [];
        $instructors = [];
        $alreadylisted = [];
        $context = \context_course::instance($courseid);
        $roles = $DB->get_records_list('role', 'shortname', ['teacher', 'editingteacher']);
        $userfields = 'u.id,u.picture,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.imagealt,u.email';

        foreach ($roles as $role) {
            if ($role) {
                $users = array_merge($users, get_role_users(roleid: $role->id, context: $context, fields: $userfields));
            }
        }

        foreach ($users as $user) {
            if (!in_array($user->id, $alreadylisted)) {
                $instructorurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]);
                $instructor = new \stdClass();
                $instructor->fullname = fullname($user);
                $instructor->picturesrc = "$CFG->wwwroot/user/pix.php/$user->id/f2.jpg";
                $instructor->instructorurl = $instructorurl->out();
                $instructors[] = $instructor;
                $alreadylisted[] = $user->id;
            }
        }

        return $instructors;
    }

    public function render_coursehint_enrol(\theme_urcourses_default\output\coursehint_enrol $coursehint_enrol) {
        $data = $coursehint_enrol->export_for_template($this);
        return $this->render_from_template('theme_urcourses_default/course-hint-enrol', $data);
    }

    public function render_coursehint_date(\theme_urcourses_default\output\coursehint_date $coursehint_date) {
        $data = $coursehint_date->export_for_template($this);
        return $this->render_from_template('theme_urcourses_default/course-hint-date', $data);
    }

    public function render_feedbackbutton(\theme_urcourses_default\output\feedbackbutton $feedbackbutton) {
        $data = $feedbackbutton->export_for_template($this);
        return $this->render_from_template('theme_urcourses_default/feedback-button', $data);
    }
}