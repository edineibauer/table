(function ($) {
    $.fn.orderBy = function (options) {
        var defaults = {
            id: this.attr("data-id") || "null",
            table: this.attr("data-table") || "null",
            callback: this.attr("data-callback") || "null",
            parameters: this.attr("data-parameters") || "null"
        };

        $.extend(defaults, options);

        if(defaults.callback === "null") {
            alert("método orderBy necessita de 'data-callback' para chamar uma função resultante. Favor corrigir isto para ordenação funcionar.");
        } else {
            if(defaults.parameters !== "null" && defaults.parameters.match(/,/i)) {
                defaults.parameters = defaults.parameters.split(',').map(function(n){return n.toString().trim()});
            }

            this.css("position", "relative");
            this.prepend("<i class='material-icons iconArrowTableList' id='iconArrowTableList-" + defaults.id + "'>arrow_drop_down</i>");

            var orderBy = {
                change: function () {
                    if ($("#order-" + defaults.table).val() === defaults.id) {
                        $("#orderAsc-" + defaults.table).val($("#orderAsc-" + defaults.table).val() === "false" ? "true" : "false");
                    } else {
                        $("#order-" + defaults.table).val(defaults.id);
                        $("#orderAsc-" + defaults.table).val(true);
                    }
                    $("#iconArrowTableList-" + defaults.id).html($("#orderAsc-" + defaults.table).val() === "false" ? "arrow_drop_down" : "arrow_drop_up");

                    window[defaults.callback](defaults.parameters);
                }
            };

            this.on("click", ".iconArrowTableList", function () {
                orderBy.change();
            });
        }

        return this;
    };
}(jQuery));