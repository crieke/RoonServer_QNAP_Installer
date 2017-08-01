function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function roon_logout() {
    Cookies.remove('roontoken');
    Cookies.remove('roonuserid');
}
function roon_token() {
    return Cookies.get('roontoken');
}
function roon_userid() {
    return Cookies.get('roonuserid');
}

function roon_code() {
    return Cookies.get('rooncode');
}
function roon_set_code(code) {
    if (code)
        Cookies.set('rooncode', code);
    else
        Cookies.remove('rooncode');
}

function roon_offers() {
    var offers = Cookies.get('roonoffers');
    if (!offers) return undefined;
    offers = JSON.parse(offers);
    return offers.offers;
}

function roon_select_offer(offer) {
    var offers = Cookies.get('roonoffers');
    if (offers) {
        offers = JSON.parse(offers);
        offers['selected'] = offer;
        Cookies.set('roonoffers', JSON.stringify(offers));
    }
}
function roon_offer() {
    var offers = Cookies.get('roonoffers');
    if (!offers) return undefined;

    offers = JSON.parse(offers);
    if (offers.offers.length == 1) return offers.offers[0];

    if (typeof(offers.selected) !== 'undefined')
        return offers.selected;

    return undefined;
}

function ga_event(o, cb) {
    if (window.ga && ga.loaded) {
        if (cb) o['hitCallback'] = cb;
        ga('send', 'event', o);
    } else {
        if (cb) cb();
    }
}

function _cleanuistate(uistate) {
    return uistate || { buttons: "", msg: "", focus: "" };
}

function roon_getoffers(token, code, retryonerrorwithoutcode, uistate, cb) {
    Cookies.remove('roonoffers');

    uistate = _cleanuistate(uistate);
    $(uistate.msg).hide();
    $(uistate.buttons).prop("disabled",true);

    var d = {
        branding: "d52b2cb7-02c5-48fc-981b-a10f0aadd93b",
        code: code
    };
    if (token) d.token = token;

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/offerquery",
        dataType: "json",
        data: d
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            // XXX set this in a way so it expires at browser session end
            Cookies.set('roonoffers', JSON.stringify({ offers: data.offers }));
            cb(data);
            return;

        } else if (data.status == "Unauthorized") {
            roon_logout();
            roon_getoffers(undefined, code, retryonerrorwithoutcode, uistate, cb);
            return;

        } else if (data.status == "CodeExpired") {
            $(uistate.msg).text("This code " + code + " has expired, and can not be used anymore.");

        } else if (data.status == "CodeInvalid") {
            $(uistate.msg).text("This code is not valid. Please check your code and try again.");

        } else if (data.status == "CodeInvalidForPartner") {
            $(uistate.msg).text("This code is not valid for partners. Did you mean to redeem this with your account?");

        } else if (data.status == "CodeAlreadyUsed") {
            $(uistate.msg).text("This code has already been used. Please enter a new code.");

        } else if (data.status == "AlreadyUsedCodeClass") {
            $(uistate.msg).html("<p>This code can't be used because you already used a similar code.</p><p>Please check <a href='account.html'>your account</a> to see the status of your membership.</p>");

        } else {
            console.log(data);
            $(uistate.msg).text("Submission failed: " + data.status + ". Please try again.");
        }

        if (code && retryonerrorwithoutcode) {
            roon_getoffers(token, undefined, false, uistate, cb);
            return;
        }

        $(uistate.msg).show();
        Cookies.remove('roonoffers');
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        Cookies.remove('roonoffers');
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).text("Submission failed due to network error. Please check your connection and try again.");
        $(uistate.msg).show();
        cb({ status: "NetworkError" });
    });
}

function roon_addcode(token, code, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/licenseadd",
        dataType: "json",
        data: {
            token: token,
            coupon: code
        }
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            roon_set_code("");
            cb(data)
            return;

        } else if (data.status == "Unauthorized") {
            $(uistate.msg).html("<p>Account credentials are invalid. Please sign in again.</p>");

        } else if (data.status == "AlreadyUsedCouponClass") {
            $(uistate.msg).html("<p>This code can't be used because you already used a similar code.</p><p>Please check <a href='account.html'>your account</a> to see the status of your membership.</p>");

        } else if (data.status == "CouponAlreadyRedeemed") {
            $(uistate.msg).html("<p>It looks like this code has already been redeemed.</p><p>Please check <a href='account.html'>your account</a> to see the status of your membership.</p>");

        } else if (data.status == "InvalidCoupon") {
            $(uistate.msg).text("This code is not valid. Please check your code and try again.");

        } else if (data.status == "CouponExpired") {
            $(uistate.msg).text("The code " + code + " has expired, and can not be used anymore.");

        } else if (data.status == "TrialOnlyForNewUser") {
            $(uistate.msg).html("<p>Only new accounts are<br>eligible for trial codes.</p><p>Please check your current membership status <a href='account.html'>here</a>.");

        } else {
            console.log(data);
            $(uistate.msg).html("There was an error processing your code. Please <a target='_blank' href='contact.html'>contact support</a> with the following information: " + rooncode.code + "#" + data.status);
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).text("We weren't able to add your license due to network error. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}

function roon_login(email, password, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/login",
        dataType: "json",
        data: {
            email: email,
            password: password
        }
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            Cookies.set('roontoken', data.token);
            Cookies.set('roonuserid', data.userid);
            cb(data)
            return;

        } else if (data.status == "NotFound") {
            $(uistate.msg).text("There is no account associated with this Email address. Check the Email address and try again.");

        } else if (data.status == "Unauthorized") {
            $(uistate.msg).text("Invalid Password. Please check your password and try again.");

        } else {
            console.log(data);
            $(uistate.msg).html("There was an error accessing your account. Please sign in again. (" + data.status + ")");
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).html("There was an error retrieving your account information. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}

function roon_get_email(token, cb) {
    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/userinfo",
        dataType: "json",
        data: {
            token: token,
        }
    })
    .done(function(data) {
        cb(data);
    })
    .fail(function(data) {
        cb(data);
    });
}
function roon_userinfo(token, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/userinfo",
        dataType: "json",
        data: {
            token: token,
        }
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            cb(data)
            return;

        } else if (data.status == "Unauthorized") {
            $(uistate.msg).html("<p>Account credentials are invalid. Please sign in again.</p>");

        } else {
            console.log(data);
            $(uistate.msg).html("There was an error retrieving your account information. Please sign in again. (" + data.status + ")");
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).html("There was an error retrieving your account information. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}

function roon_createuser(data, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    data.branding = "d52b2cb7-02c5-48fc-981b-a10f0aadd93b";
    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/usercreate",
        dataType: "json",
        data: data
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            cb(data)
            return;

        } else if (data.status == "InvalidName") {
            $(uistate.msg).text("Invalid Name. Please check your first and last name and try again.");

        } else if (data.status == "EmailExists") {
            $(uistate.msg).text("An account with this Email already exists. Try to sign in instead. If you forgot your password, just click the link to reset you password.");

        } else if (data.status == "InvalidEmail") {
            $(uistate.msg).text("Invalid Email. Please check your Email address and try again.");

        } else if (data.status == "InvalidPassword") {
            $(uistate.msg).text("Invalid Password. Please check your password and try again.");

        } else {
            $(uistate.msg).text("Create Account failed: " + data.status + ". Please try again.");
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).text("Create Account failed due to network error. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}

function roon_partner_email_info(branding, email, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/partneremailinfo",
        dataType: "json",
        data: {
            branding: branding,
            email: email
        }
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            cb(data)
            return;

        } else if (data.status == "InvalidEmail") {
            $(uistate.msg).text("Invalid Email. Please check your Email address and try again.");

        } else {
            $(uistate.msg).text("Partner Login failed: " + data.status + ". Please try again.");
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).text("Partner Login failed due to network error. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}

function roon_task_submit(type, title, commands, uistate, cb) {
    uistate = _cleanuistate(uistate);
    $(uistate.buttons).prop("disabled",true);
    $(uistate.msg).hide();

    $.ajax({
        type: "POST",
        url: "https://accounts5.roonlabs.com/accounts/3/taskadd",
        dataType: "json",
        data: { type: type, title: title, commands: JSON.stringify(commands) }
    })
    .done(function(data) {
        $(uistate.buttons).prop("disabled",false);

        if (data.status == "Success") {
            cb(data)
            return;

        } else {
            $(uistate.msg).text("Submission failed: " + data.status + ". Please try again.");
        }

        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    })
    .fail(function(data) {
        $(uistate.buttons).prop("disabled",false);
        $(uistate.msg).text("Submission failed due to network error. Please check your connection and try again.");
        $(uistate.msg).show();
        $(uistate.focus).focus();
        cb(data);
    });
}
