if (typeof tableNovo !== 'function') {
    var loadTime = searchTime = false;

    // $("head").append("<link rel='stylesheet' href='" + HOME + "vendor/conn/table/assets/table.min.css' />");

    function tableNovo(entity) {
        $("#btn-table-" + entity).panel(themeWindow("Novo " + entity, {
            lib: 'form-crud',
            file: 'read/form',
            entity: entity
        }, function () {
            loadingTable(entity);
            resetPagination(entity);
            readTable(entity);
        }));
    }

    function editEntityData(entity, id) {
        var $form = $("#table-" + entity);
        var cont = $form.find(".table-select:checked").length;
        if (cont > 1 && cont < 5) {
            $.each($form.find(".table-select"), function () {
                if ($(this).is(":checked"))
                    editEntityDataId(entity, parseInt($(this).attr("rel")));
            });
        } else {
            editEntityDataId(entity, id)
        }
    }

    function deleteEntityData(entity, id) {
        var $form = $("#table-" + entity);
        var cont = $form.find(".table-select:checked").length;

        if (confirm(cont > 1 ? "Excluir os " + cont + " Registros?" : "Excluir este Registro? ")) {
            loadingTable(entity);

            if (($form.find("tr").length - (cont === 0 ? 1 : cont)) === 1) {
                resetPagination(entity);
                readTable(entity);
            }

            if (cont > 1) {
                $.each($form.find(".table-select"), function () {
                    if ($(this).is(":checked"))
                        deleteEntityDataId(entity, parseInt($(this).attr("rel")));
                });
            } else {
                deleteEntityDataId(entity, id);
            }
        }
    }

    function duplicateEntityData(entity, id) {
        loadingTable(entity);
        resetPagination(entity);

        var $form = $("#table-" + entity);
        if ($form.find(".table-select:checked").length > 1) {
            $.each($form.find(".table-select"), function () {
                if ($(this).is(":checked"))
                    duplicateEntityDataId(entity, parseInt($(this).attr("rel")));
            });
        } else {
            duplicateEntityDataId(entity, id);
        }
    }

    function editEntityDataId(entity, id) {
        $("#edit-" + entity + "-" + id).panel(themeWindow("Editar " + entity, {
            lib: 'form-crud',
            file: 'read/form',
            entity: entity,
            id: id
        }, function () {
            loadingTable(entity);
            readTable(entity);
        }));
    }

    function deleteEntityDataId(entity, id) {
        post('table', 'delete/data', {entity: entity, id: id}, function () {
            clearInterval(loadTime);
            $("#row-" + entity + "-" + id).remove();
        });
    }

    function duplicateEntityDataId(entity, id) {
        post('table', 'duplicate/data', {entity: entity, id: id}, function () {
            readTable(entity);
        });
    }

    function readTable(entity) {
        var limit = $("#limit-" + entity).val();
        var search = $("#search-" + entity).val();
        var offset = $("#table-pagina-" + entity).val();
        post('table', 'read/data', {
            entity: entity,
            limit: limit,
            offset: offset,
            filter: {title: search}
        }, function (data) {
            clearInterval(loadTime);
            var $table = $("#table-" + entity);
            $table.find("tr").not(":eq(0)").remove();
            $table.append(data.content);
            $("#table-cont-pag-" + entity).html(data.pagination + " \\");
            $("#table-total-" + entity).html(data.total);

            if ($('#pagination-' + entity).html() === "" && data.pagination > 1) {
                $('#pagination-' + entity).materializePagination({
                    lastPage: data.pagination,
                    onClickCallback: function (requestedPage) {
                        if (parseInt($("#table-pagina-" + entity).val()) !== requestedPage) {
                            $("#tableList-" + entity).loading();
                            $("#table-pagina-" + entity).val(requestedPage);
                            readTable(entity);
                        }
                    }
                });
            }
        });
    }

    function loadingTable(entity) {
        $("#tableList-" + entity).loading();
        loadTime = setInterval(function () {
            $("#tableList-" + entity).loading();
        }, 2000);
    }

    function resetPagination(entity) {
        $('#pagination-' + entity).html("");
        $("#table-pagina-" + entity).val(1);
        $("#table-" + entity).find(".table-select-all").prop("checked", false);
    }

    function startTable() {
        $.each($(".tableList"), function () {
            readTable($(this).attr("data-entity"));
        });

        $(".tableLimit").off("change").on("change", function () {
            var entity = $(this).attr("data-entity");
            loadingTable(entity);
            resetPagination(entity);
            readTable(entity);
        });

        $(".table-search").off("keyup change").on("keyup change", function () {
            var entity = $(this).attr("data-entity");
            clearTimeout(searchTime);
            searchTime = setTimeout(function () {
                loadingTable(entity);
                readTable(entity);
            }, 400);
        });

        $(".table-select-all").off("change").on("change", function () {
            $("#table-" + $(this).attr("data-entity")).find(".table-select").prop("checked", $(this).is(":checked"));
        });

        $(".table-all").off("change", ".table-select").on("change", ".table-select", function () {
            var all = true;
            var $this = $(this);
            var $form = $("#table-" + $(this).attr("data-entity"));

            $.each($form.find(".table-select"), function () {
                if (all && $(this).is(":checked") !== $this.is(":checked"))
                    all = false;
            });

            $form.find(".table-select-all").prop("checked", (all && $this.is(":checked")));
        });
    }
}

$(function () {
    startTable();
});

/*

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

            var $icon = $("<i class='material-icons hide editFieldIcon'>" + defaults.icon + "</i>").appendTo(this);

            this.off("mouseover focus").on("mouseover focus", function () {
                $icon.removeClass("hide");
            }).off("mouseleave").on("mouseleave", function () {
                $icon.addClass("hide");
            });

            $icon.off("click").on("click", function () {
                editField.open(this);
            });

            var editField = {
                open: function ($this) {
                    var valor = $this.html();
                    $this.html("<input type='text' style='height: 35px; width: " + $this.width() + "px' rel='" + defaults.id + "' class='inputEditableTableList' placeholder='" + defaults.content + "' id='searchTableList-" + defaults.id + "' />");
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
}(jQuery));*/
