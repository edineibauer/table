{foreach $values as $dado}
    <tr>
        <td class="teste">
            <input type="checkbox" class="selectTableList" data-table="{$info['table']}" rel="{$dado[$info['primary']]}"
                   id="select-{$info['table']}-{$dado[$info['primary']]}"/>
            <label for="select-{$info['table']}-{$dado[$info['primary']]}"></label>
        </td>
        {foreach $struct as $item}
            {if $item['list'] && isset($dado[$item['column']])}
                <td>{$dado[$item['column']]}</td>
            {/if}
        {/foreach}

        <td class="right-align">
            <button title="editar" onclick="editarTableList({$dado[$info['primary']]}, '{$info['table']}')"
                    class="waves-effect waves-light btn-flat pd-horizonte1 waves-orange"><i
                        class="material-icons">edit</i></button>
            <button title="duplicar" onclick="copyTableList({$dado[$info['primary']]}, '{$info['table']}')"
                    class="waves-effect waves-light btn-flat pd-horizonte1 waves-green"><i
                        class="material-icons">content_copy</i></button>
            <button title="remover" onclick="removeTableList({$dado[$info['primary']]}, '{$info['table']}')"
                    class="waves-effect waves-light btn-flat pd-horizonte1 waves-red"><i
                        class="material-icons">delete</i></button>
        </td>

        <td class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></td>
    </tr>
{/foreach}