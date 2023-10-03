var Shoutbox = {

    name: null,
    
    intervalHolder: null,
    
    maxMessages: 500,
    
    getName: function() {
        return this.name;
    },
    
    setName: function(name) {
        this.name = name;
    },
    
    getTimestamp: function() {
        return parseInt(new Date().getTime() / 1000);
    },
    
    updateCurrentTimestamp: function() {
        this.currentTimestamp = this.getTimestamp();
    },
    
    isEmpty: function(string) {
        return string == '';
    },
    
    isBot: function(string) {
        return string == 'Bot';
    },
    
    alert: function(content) {
        alert(content);
    },
    
    reload: function() {
        window.location.reload();
    },
    
    save: function() {
        // Protection
        if (this.currentTimestamp > (this.getTimestamp() - this.spamProtectionRate)) {
            this.alert('Please wait at least ' + this.spamProtectionRate + ' seconds before you shout again.');
            this.updateCurrentTimestamp();
        
            return false;
        }
        
        // Update timestamp
        this.updateCurrentTimestamp();
        
        // Post message to server
        var $shoutboxMessage = $('#shoutbox-message');
        $.post('ajax.php', {
            action: 'post',
            name: Shoutbox.getName(),
            message: $shoutboxMessage.val()
        }, function() {});
        $shoutboxMessage.val('');
    },
    
    bot: function(name) {
        this.setName(name);
        // Post bot message to server
        $.post('ajax.php', {
            action: 'bot',
            name: Shoutbox.getName()
        }, function() {
            Shoutbox.update();
        });
    },
    
    update: function() {
        // Update shoutbox with latest messages
        var $shoutbox = $('#shoutbox-box');
        var $shoutboxMessageContainer = $shoutbox.find('div');
        var $shoutboxMessageLastContainer = $shoutboxMessageContainer.filter(':last');
        $.get('ajax.php', {
            action: 'update',
            last: $shoutboxMessageLastContainer.attr('id')
        }, function(html) {
            if ($shoutboxMessageContainer.size() > Shoutbox.maxMessages) {
                Shoutbox.reload();
            }
            if (html.length > 0) {
                $shoutbox.append(html).animate({ scrollTop: $shoutbox.prop('scrollHeight') }, 1200);
                $shoutbox.find('div:odd').addClass('odd');
            }
        }, 'html');
    
        return true;
    },
    
    loader: function() {
        // Hide old interface
        $('#shoutbox-enter-name').fadeOut('slow').remove();
        $('#shoutbox-loader').fadeIn('slow');
        
        // Load shoutbox interface
        $.get('ajax.php', {
            action: 'loader'
        }, function(data) {
            // Disable ajax caching
            $.ajaxSetup({ cache: false });
            // Hide old login interface
            $('#shoutbox-loader').fadeOut('slow', function() {
                // Insert new interface HTML code
                $('#shoutbox-box-wrapper').html(data);
                // Start interval for updates
                Shoutbox.intervalHolder = window.setInterval(Shoutbox.update, Shoutbox.refreshRate);
                // Show new interface
                $('#shoutbox-box-inner').animate({ opacity: 1 }, 'slow');
                
                // Set often used jQuery elements
                var $shoutboxMessage = $('#shoutbox-message');
                var $shoutboxSubmit = $('#shoutbox-submit');
                var $shoutboxForm = $('#shoutbox-form');
                
                // Attach message counter and save message on submit
                $shoutboxMessage.focus().charcounter().on('focus', function() {
                    var $this = $(this);
                    if ($this.val() == "\n") {
                        $this.val('');
                    }
                }).on('keypress', function (e) {
                    if (e.which == 13 && e.shiftKey == false && $.trim($(this).val()) != '') {
                        Shoutbox.save();
                        e.preventDefault();
                    }
                });
                
                // Save message also on submit button click or form submit
                $shoutboxSubmit.on('click', function() {
                    Shoutbox.save();
                    return false;
                });
                $shoutboxForm.on('submit', function() {
                    Shoutbox.save();
                    return false;
                });
            });
        });
    },
    
    setupShoutbox: function() {
        var $shoutboxName = $('#shoutbox-name');
        var $shoutboxNameSubmit = $('#shoutbox-name-submit');
        
        $shoutboxNameSubmit.on('click', function () {
            var shoutboxName = $('#shoutbox-name').val();
            if (!Shoutbox.isEmpty(shoutboxName) && !Shoutbox.isBot(shoutboxName)) {
                Shoutbox.bot(shoutboxName);
                Shoutbox.loader();
            }
        });
        
        // If name input is prepopulated
        var shoutboxNamePrepopulate = $shoutboxName.data('prepopulate');
        if (shoutboxNamePrepopulate != '') {
            $shoutboxName.val(shoutboxNamePrepopulate);
            $shoutboxNameSubmit.trigger('click');
        }
        
        $shoutboxName.placeholder('Nickname').on('keypress', function (e) {
            var nameValue = $(this).val();
            if (e.which == 13 && !Shoutbox.isEmpty(nameValue) && !Shoutbox.isBot(nameValue)) {
                Shoutbox.bot(nameValue);
                Shoutbox.loader();
            }
        });
    },
    
    init: function() {
        this.updateCurrentTimestamp();
        this.setupShoutbox();
    }
};


jQuery(document).ready(function($) {
    Shoutbox.init();
});