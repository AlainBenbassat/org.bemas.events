CRM.$(function($) {

  // add event handler to email field
  $("#email-Primary").focusout(function() {
    // get filled in email address
    var email = $("#email-Primary").val();

    if (email) {
      // get contact
      CRM.api3("Contact", "getparticipantinfo", {
        "sequential": 1,
        "email": email
      }).then(function(result) {
        if (result.is_error == 0) {
          var contact = result.values[0];

          // fill in the org. name
          $("#current_employer").val(contact.organization_name);

          // add the name to eu.tttp.publicautocomplete
          publicautocomplete.matchedValues[contact.organization_name] = true;

          // fill in the billing or main address
          if (String.prototype.trim($("#custom_95").val()) == "") {
            if (contact.billing_address != "") {
              $("#custom_95").val(contact.billing_address);
            }
            else if (contact.main_address != "") {
              $("#custom_95").val(contact.main_address);
            }
          }

          // fill in the VAT
          if (String.prototype.trim($("#custom_94").val()) == "") {
            if (contact.vat != "") {
              $("#custom_94").val(contact.vat);
            }
          }

          // select the first price set if the contact is a member
          if (contact.membership_count > 0) {
            $("div.Deelname-row1 span input").prop('checked', true);
          }
          else {
            // not a member, check the second price
            $("div.Deelname-row2 span input").prop('checked', true);
          }

        }
        else {
          console.log(result.error_message);
          $("div.Deelname-row2 span input").prop('checked', true);
          $("#current_employer").val("");
        }
      }, function(error) {
      });
    }

  });
});

