(function ($, STUDIP) {
    'use strict';
    STUDIP.Veranstaltungsanmeldung = {

        init: function () {
            if ($('#create-coursedates').is(':checked')) {
                $('.manual-time').hide()
            }
            $('#create-coursedates').on(
                'click', function () {
                $('.manual-time').hide()
            });
            $('#create-manual').on('click', function () {
                $('.manual-time').show()
            });
        },

        /**
         * Adds a new participating institute to the thesis topic studydata.
         * @param id Stud.IP institute ID
         * @param name Full name
         * @param inputName name of the for input to generate
         * @param elClass desired CSS class name
         * @param elId ID of the target container to append to
         * @param otherInput name of other inputs to check
         *
         *                   (e.g. deputies if adding a lecturer)
         */
        addParticipatingInst: function (id, name) {
            // Check if already set.
            if ($('input[name="participating[' + id + ']"]').length == 0) {
                var wrapper = $('<div>').addClass('institute');
                $('#wizard-participating')
                    .children('div.description')
                    .removeClass('hidden-js');
                var input = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'participating[' + id + ']')
                    .attr('id', id)
                    .attr('value', '1');
                var trash = $('<input>')
                    .attr('type', 'image')
                    .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg')
                    .attr('name', 'remove_participating[' + id + ']')
                    .attr('value', '1')
                    .attr('onclick', "return STUDIP.Veranstaltungsanmeldung.removeParticipatingInst('" + id + "')");
                wrapper.append(input);
                var nametext = $('<span>')
                    .html(name)
                    .text();
                wrapper.append(nametext);
                wrapper.append(trash);
                $('#wizard-participating').append(wrapper);
            }
        },

        /**
         * Remove a participating institute from the list.
         * @param id ID of the institute to remove
         * @returns {boolean}
         */
        removeParticipatingInst: function (id) {
            var parent = $('input#' + id).parent();
            var grandparent = parent.parent();
            parent.remove();
            if (grandparent.children('div').length == 0) {
                grandparent.children('div.description').addClass('hidden-js');
            }
            return false;
        },

        /**
         * Adds a new person to the course.
         * @param id Stud.IP user ID
         * @param name Full name
         * @param inputName name of the for input to generate
         * @param elClass desired CSS class name
         * @param elId ID of the target container to append to
         * @param otherInput name of other inputs to check
         *
         */
        addPerson: function (id, name, inputName, elClass, elId, otherInput) {
            // Check if already set.
            if ($('input[name="' + inputName + '[' + id + ']"]').length == 0) {
                var wrapper = $('<div>').addClass(elClass);
                $('#' + elId)
                    .children('div.description')
                    .removeClass('hidden-js');
                var input = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', inputName + '[' + id + ']')
                    .attr('id', id)
                    .attr('value', '1');
                var trash = $('<input>')
                    .attr('type', 'image')
                    .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg')
                    .attr('name', 'remove_' + elClass + '[' + id + ']')
                    .attr('value', '1')
                    .attr('onclick', "return STUDIP.Veranstaltungsanmeldung.removePerson('" + id + "')");
                wrapper.append(input);
                var nametext = $('<span>')
                    .html(name)
                    .text();
                wrapper.append(nametext);
                wrapper.append(trash);
                $('#' + elId).append(wrapper);
                // Remove as deputy if set.
                $('input[name="' + otherInput + '[' + id + ']"]')
                    .parent()
                    .remove();
            }
        },

        /**
         * Adds a new lecturer to the course.
         * @param id Stud.IP user ID
         * @param name Full name
         */
        addLecturer: function (id, name) {
            STUDIP.Veranstaltungsanmeldung.addPerson(id, name, 'lecturers', 'lecturer', 'wizard-lecturers', 'deputies');
        },

        addModel: function (id, name) {
            STUDIP.Veranstaltungsanmeldung.addPerson(id, name, 'models', 'model', 'wizard-models', 'lecturers');
        },

        addCourse: function (id, name) {
            STUDIP.Veranstaltungsanmeldung.addPerson(id, name, 'courses', 'course', 'wizard-courses', 'deputies');
        },

        /**
         * Remove a person (lecturer) from the list.
         * @param id ID of the person to remove
         * @returns {boolean}
         */
        removePerson: function (id) {
            var parent = $('input#' + id).parent();
            var grandparent = parent.parent();
            parent.remove();
            if (grandparent.children('div[class!="description"]').length == 0) {
                grandparent.children('div.description').addClass('hidden-js');
            }
            return false;
        },
    };

    STUDIP.ready(function () {
        STUDIP.Veranstaltungsanmeldung.init();
        $(document).on('dialog-update', function () {
            STUDIP.Veranstaltungsanmeldung.init();
        });
    });

}(jQuery, STUDIP));
