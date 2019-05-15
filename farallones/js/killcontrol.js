var KillControl = function () {
	var $ = jQuery;
	var _isSubmitting = false;
	var _ajaxUrl = "json/";
	var _copy = {
		genericError: "Unknown server error. Check your network connetion and try again.",
		errorMessage: "Oops, there was a problem processing your request. The server returned the following error(s):",
		successMessage: "You have successfully disabled public control of the camera. Reactivation time is estimated at:" 
	};
	
	/**
     * Debug utility
	 * @param {Object} Generic object to log
	 */
	var _log = function (obj) {
		if (typeof(console) == "undefined") {
			if (typeof(dump) == "function") {
				dump(obj);
			} else {
				//alert(obj);
			}
		} else {
			console.log(obj);
		}
	}
	
	var _processing = function (boo) {
		if (boo) {
			$("#submit").hide();
			$("#processing").show();
		} else {
			$("#submit").show();
			$("#processing").hide();
		}
	}
	
	var _isValidInt = function (selector) {
		var val = parseInt($(selector).val());
		
		if (!isNaN(val)) {
			if (val > 0) {
				// is a number and greater than zero
				return true;
			}
		}
		
		return false;
	}
	
	var _isValid = function () {
		return (_isValidInt("#uid_cam") && _isValidInt("#minutes"));
	}
	
	/**
	 * Handle form submission via JSON
	 * @private
	 */
	var _initForm = function () {
		$("form").submit(function () {
			// suppress crazy clicks
			if (_isSubmitting) return false;
			
			if (!_isValid()) {
				_onSubmitError(["Please make a selection."]);
				return false;
			}
			
			_isSubmitting = true;
			_processing(true);
			
			var submitData = {
				uid_cam: $("#uid_cam").val(),
				minutes: $("#minutes").val()
			};
			
			if (typeof($.jsonp) == "function") {
				// use the jsonp plugin if available
				$.jsonp({
					timeout: 30000,
					callbackParameter: "callback",
					url: _ajaxUrl,
					data: submitData,
					success: function (data, textStatus) {
						_onSubmitSuccess(data);
					},
					error: function (options, textStatus) {
						_onSubmitError();
					}
				});
			} else {
				$.ajax({
					dataType: "jsonp",
					url: _ajaxUrl,
					cache: false,
					data: submitData,
					success: function (data, textStatus, XMLHttpRequest) {
						_onSubmitSuccess(data);
					},
					error: function (XMLHttpRequest, textStatus, errorThrown) {
						_onSubmitError();
					}
				});
			}
		   
			// always suppress submission and await response
			return false;
		});
	}
	
	var _onSubmitSuccess = function (data) {
		_log("_onSubmitSuccess");
		_log(data);
		
		// invalid server response
		if (typeof(data) != "object" || typeof(data.success) == "undefined") {
			_onSubmitError();
			return;
		}
		
		if (data.success) {
			// reset form
			$("#minutes").val("");
			_displayResponse("<p>" + _copy.successMessage + "</p><ul><li>" + data.time_end + "</li></ul>");
		} else {
			_onSubmitError(data.errors);
		}
		
		_isSubmitting = false;
		_processing(false);
	}
	
	/**
	 * Handle submissions errors
	 * @param errors {Array} An array of error messages
	 * @private
	 */
	var _onSubmitError = function (errors) {
		_log("_onSubmitError");
		
		if (!ValidationUtil.isArray(errors) || errors.length < 1) {
			errors = [_copy.genericError];
		}
		
		// construct html response
		var html = "<p>" + _copy.errorMessage + "</p>";
		html += "<ul>";
		
		var i = 0;
		
		while (i < errors.length) {
			html += "<li>" + errors[i] + "</li>";
			i++;
		}
		
		html += "</ul>";
		
		// display
		_displayResponse(html);
		
		_isSubmitting = false;
		_processing(false);
	}
	
	var _displayResponse = function (str) {
		$(".server-response").remove();
		
		var response = $("<div class='server-response' />");
		response.html(str);
		$("form").before(response);
	}
	
	
	/**
	 * @constructor
	 */
	this.initialize = function () {
		_initForm();
	}
	
	this.initialize();
}

if (typeof(jQuery) != "undefined") {
	jQuery(document).ready(function ($) {
		var foo = new KillControl();
	});
}
