<extends:partials.element/>

<block:body>
    <label class="item-form ${wrapper-class}" node:attributes="prefix:wrapper">
        <?php #compiled
        //Receiving label content as evaluator variable
        $this->evaluatorVariable('label', '${label}');
        if (!empty($label) && $label != "''") {
            ?>
            <block:input-label>
                <span class="${label-class} item-label" node:attributes="prefix:label">${label}</span>
            </block:input-label>
            <?php #compiled
        }

        /** @var mixed $inputValue */
        $this->runtimeVariable('inputValue', '${value}${context}');
        ?>
        <block:input-body>
                <input type="${type|text}"
                       name="${name}"
                       value="<?= $inputValue ?>"
                       data-prefix="${prefix}"
                       data-pattern="${pattern}"
                       class="item-input <?=(!empty($prefix) && $prefix != "''") || (!empty($pattern) && $pattern != "''") ? 'sf-js-input' : '' ?>"
                       node:attributes="exclude:wrapper-*"/>
        </block:input-body>
    </label>
</block:body>