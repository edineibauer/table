(function ($) {
    $.fn.editField = function (options) {
        var defaults = {
            icon: this.attr("data-icon") || "edit",
            id: this.attr("data-id") || "null",
            content: this.attr("data-content") || this.html(),
            table: this.attr("data-table") || "null",
            callback: this.attr("data-callback") || "null",
            parameters: this.attr("data-parameters") || "null",
            isOpen: false
        };

        $.extend(defaults, options);

        if (defaults.callback === "null") {
            alert("data-callback ausente na aplicação do plugin editField. Favor informar para funcinamento do plugin.");
        } else {

            this.attr("id", "editableField-" + defaults.id).addClass("noselect");
            this.append("<div class='textEditableField' alt='" + defaults.content + "' id='textEditableField-" + defaults.id + "' rel='" + defaults.id + "'>" + defaults.content + "</div>");
            this.append("<i class='material-icons left hoverI iconEditableField' id='editableTableListButton-" + defaults.id + "'>" + defaults.icon + "</i>");

            var editField = {
                open: function () {
                    var $element = $("#editableField-" + defaults.id);
                    var width = $element.find(".textEditableField").width();
                    $element.find("i").removeClass("hoverI");
                    $element.find(".textEditableField").css("display", "none");
                    $element.prepend("<input type='text' style='height: 35px; width: " + width + "px' rel='" + defaults.id + "' class='inputEditableTableList' placeholder='" + defaults.content + "' id='searchTableList-" + defaults.id + "' />");
                    $element.find(".inputEditableTableList").focus();
                },
                close: function () {
                    var $element = $("#editableField-" + defaults.id);
                    $element.find(".iconEditableField").addClass("hoverI");
                    $element.find(".inputEditableTableList").remove();
                    $element.find(".textEditableField").css("display", "inline");
                    defaults.isOpen = false;
                },
                search: function () {
                    var $element = $("#editableField-" + defaults.id);
                    $element.find(".inputEditableTableList").focus();
                    $("#offset-" + defaults.table).val(1);
                    window[defaults.callback](defaults.parameters);
                }
            };

            this.on("click", ".iconEditableField", function () {
                if (!defaults.isOpen) {
                    editField.open();
                    defaults.isOpen = true;
                } else {
                    editField.search();
                }
            });

            this.on("keyup", ".inputEditableTableList", function (event) {
                if (event.which === 13) {
                    editField.search();
                }
            });

            this.on("blur", ".inputEditableTableList", function () {
                if ($(this).val().length < 1) {
                    setTimeout(function () {
                        editField.close();
                    }, 100);
                }
            });
        }
        return this;
    };
}(jQuery));