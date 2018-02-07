<div class="responsive">
    <div class="row panel">
        <label class="left">
            <input type="text" class="font-size15" placeholder="busca..." style="margin-bottom: 0"/>
        </label>

        <select class="left" style="width: auto;margin-bottom: 0;margin-top: -4px;">
            <option value="0" disabled="disabled">selecione</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
        </select>

        <span class="padding-medium color-text-grey left">{$total}</span>


        <button class="btb right color-teal" id="btn-table-{$entity}" onclick="novo('{$entity}')">
            <i class="material-icons left">add</i><span class="left">Novo</span>
        </button>
    </div>
    <table class="table-all" id="table-{$entity}">
        <tr>
            {foreach item=item key=i from=$header}
                <th>{if $i === 0}
                        <label class="left">
                        <input type="checkbox" class="table-select-all" rel="{$entity}" style="margin: 15px 2rem 11px 0px;"/>
                        </label>
                    {/if}{$item}</th>
            {/foreach}
        </tr>
    </table>

    <script src="{$home}assets/table.js" defer></script>
</div>