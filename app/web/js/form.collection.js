// js/form.collection.js
function FormCollection(div_id)
{
    // keep reference to self in all child functions
    var self=this;

    self.construct = function () {
        // set some shortcuts
        self.div = $('#'+div_id);
        self.div.data('index', self.div.find(':input').length);

        // add delete link to existing children
        self.div.children().each(function() {
            self.addDeleteLink($(this));
        });

        // add click event to the Add new button
        self.div.next().on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            self.addNew();
        });
    };

    /**
     * onClick event handler -- adds a new input
     */
    self.addNew = function () {
        // Get the data-prototype explained earlier
        var prototype = self.div.data('prototype');

        // get the new index
        var index = self.div.data('index');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        self.div.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        self.div.append($(newForm));

        // add a delete link to the new form
        self.addDeleteLink( $(self.div.children(':last-child')[0]) );

        // not a very nice intergration.. but when creating stuff that has help icons,
        // the popovers will not automatically be instantiated
        //initHelpPopovers();

        return $(newForm);
    };

    /**
     * add Delete icon after input
     * @param Element row
     */
    self.addDeleteLink = function (row) {
        var $removeFormA = $('<a href="#" class="btn btn-danger" tabindex="-1"><i class="entypo-trash"></i></a>');
        $(row).find('select').after($removeFormA);
        row.append($removeFormA);
        $removeFormA.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            row.remove();
        });
    };

    self.construct();
}