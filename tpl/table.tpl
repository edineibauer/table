<div class="responsive tableList" id="tableList-{$entity}" data-entity="{$entity}">
    <div class="row panel">

        <div class="font-xlarge left">{$entity}</div>

        <span class="padding-medium color-text-grey left">
            <b id="table-total-{$entity}">{$total}</b> registros</span>

        <button class="btb right theme-d2 hover-shadow opacity hover-opacity-off" id="btn-table-{$entity}" onclick="tableNovo('{$entity}')">
            <i class="material-icons left">add</i><span class="left">Novo</span>
        </button>

        <label class="right">
            <input type="text" class="table-search" autocomplete="nope" id="search-{$entity}" data-entity="{$entity}"
                   placeholder="busca..." style="margin-bottom: 0;font-size:14px"/>
        </label>

        <select class="right tableLimit" id="limit-{$entity}" data-entity="{$entity}"
                style="width: auto;margin-bottom: 0;margin-top: -4.5px;">
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
        </select>

        <span class="padding-medium color-text-grey right table-cont-pag" id="table-cont-pag-{$entity}"></span>

    </div>
    <table class="table-all" id="table-{$entity}">
        <tr>
            {foreach item=item key=i from=$header}
                <th>{if $i === 0}
                        <label class="left">
                            <input type="checkbox" class="table-select-all" data-entity="{$entity}"
                                   style="margin: 15px 2rem 11px 0px;"/>
                        </label>
                    {/if}
                    <span>{$item}</span>
                </th>
            {/foreach}
            <th class="align-right" style="padding-right: 20px;">Ações</th>
        </tr>
    </table>

    <div class="row panel" id="pagination-{$entity}"></div>

    <input type="hidden" class="table-pagina" value="1" data-entity="{$entity}" id="table-pagina-{$entity}"/>

    <script src="{$home}vendor/conn/table/assets/table.min.js" defer></script>
    <script src="{$home}vendor/conn/table/assets/pagination.min.js" defer></script>
</div>