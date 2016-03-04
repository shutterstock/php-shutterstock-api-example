$(function () {
  var detailTemplate = $('.detail-template');
  var purchaseTemplate = $('.purchase-template');
  var selectedImage;

  getCategories();
  performSearch($('#search-form'));
  $('#search-form').on('submit', function (e) {
    e.preventDefault();
    performSearch($(this));
  });

  $('#search-results').on('click', '.image img', function (e) {
    e.preventDefault();
    getImageDetails($(this).data('id'));
  });

  $(detailTemplate).on('click', '#purchase', function (e) {
    e.preventDefault();
    selectedImage = $(this).data('id');
    detailTemplate.modal('hide');
    purchaseTemplate.modal();

    if (Cookies.get('token')) {
      getSubscriptions();
    } else {
      getOAuthAuthorization();
    }
  });

  $(purchaseTemplate).on('submit', '#subscription-form', function (e) {
    e.preventDefault();
    performPurchase($(this).find('[name="subscription"]').val());
  });

  function getCategories() {
    $.getJSON('search/get-categories.php')
      .done(function (data) {
        var categorySelect = $('#category');
        $.each(data, function (i, category) {
          categorySelect.append(new Option(category.name, category.id));
        });
      })
      .fail(function (jqxhr, textStatus, error) {
        console.log('Request failed: ' + textStatus);
      });
  }

  function performSearch(searchForm) {
    var resultContainer = $('#search-results');
    resultContainer.html('<p>Loading search results.</p>');
    $.getJSON('search/perform-search.php', searchForm.serialize())
      .done(function (data) {
        resultContainer.empty();
        if (!data.length) {
          resultContainer.append('<p>No results found.</p>');
          return;
        }
        $.each(data, function (i, image) {
          var renderedImage = renderImagePreview(image);
          if (!renderedImage) {
            return;
          }
          resultContainer.append(renderedImage);
        });
      })
      .fail(function (jqxhr, textStatus, error) {
        resultContainer.html('<p>Error while performing search.</p>');
        console.log('Request failed: ' + textStatus);
      });
  }

  function getImageDetails(imageId) {
    detailTemplate
      .find('h3')
      .html('Loading ' + imageId + '...');

    detailTemplate
      .find('.modal-body')
      .html('<i class="fa fa-5x fa-spinner fa-spin"></i>');

    detailTemplate.modal();

    $.getJSON('search/get-image-details.php', {id: imageId})
      .done(function (data) {
        renderImageDetails(detailTemplate, data);
      })
      .fail(function (jqxhr, textStatus, error) {
        detailTemplate
          .find('.modal-body')
          .html('<p>Error while looking up image details.</p>');

        console.log('Request failed: ' + textStatus);
      });
  }

  function getOAuthAuthorization() {
    purchaseTemplate
      .find('h3')
      .html('Loading Authorization');

    purchaseTemplate
      .find('.modal-body')
      .html('<i class="fa fa-5x fa-spinner fa-spin"></i>');

    $.getJSON('oauth/get-oauth-authorization.php')
      .done(function (data) {
        $.oAuthPopup({
          path: data.url,
          name: 'ShutterstockAuth',
          callback: function () {
            getSubscriptions();
          }
        });
      })
      .fail(function (jqxhr, textStatus, error) {
        purchaseTemplate
          .find('.modal-body')
          .html('<p>Error while talking to OAuth.</p>');

        console.log('Request failed: ' + textStatus);
      });
  }

  function getSubscriptions() {
    purchaseTemplate
      .find('h3')
      .html('Loading Subscriptions');

    purchaseTemplate
      .find('.modal-body')
      .html('<i class="fa fa-5x fa-spinner fa-spin"></i>');

    $.getJSON('purchase/get-subscriptions.php')
      .done(function (data) {
        renderSubscriptionChoices(purchaseTemplate, data);
      })
      .fail(function (jqxhr, textStatus, error) {
        purchaseTemplate
          .find('.modal-body')
          .html('<p>Error while fetching subscriptions.</p>');

        console.log('Request failed: ' + textStatus);
      });
  }

  function performPurchase(subscriptionId) {
    purchaseTemplate
      .find('h3')
      .html('Purchasing image');

    purchaseTemplate
      .find('.modal-body')
      .html('<i class="fa fa-5x fa-spinner fa-spin"></i>');

    $.get('purchase/perform-purchase.php', {
      subscription: subscriptionId,
      image: selectedImage
    })
      .done(function (data) {
        purchaseTemplate.modal('hide');
        window.open(data.url, '_self');
      })
      .fail(function (jqxhr, textStatus, error) {
        purchaseTemplate
          .find('.modal-body')
          .html('<p>Error while making purchase request.</p>');

        console.log('Request failed: ' + textStatus);
      });
 }

  function renderImagePreview(image) {
    if (!image || !image.id || !image.description || !image.thumb) {
      return;
    }

    var wrapper = $('<div>');
    var thumbWrapper = $('<div>');
    var thumbnail = $('<img>');

    $(thumbnail)
      .data('id', image.id)
      .attr('src', image.thumb)
      .attr('title', image.description);

    $(thumbWrapper)
      .addClass('thumbnail-crop')
      .css('height', image.size.height)
      .css('width', image.size.width)
      .append(thumbnail);

    $(wrapper)
      .addClass('image-float-wrapper image')
      .append(thumbWrapper);

    return wrapper;
  }

  function renderImageDetails(template, image) {
    var keywords = $('<p><strong>Keywords</strong> <small></small></p>');
    var thumbWrapper = $('<div>');
    var thumbnail = $('<img>');

    $(thumbnail)
      .attr('id', image.id)
      .attr('src', image.preview)
      .attr('title', image.description);

    $(thumbWrapper)
      .addClass('thumbnail-crop')
      .css('height', image.size.height)
      .css('width', image.size.width)
      .append(thumbnail);

    $(keywords)
      .find('small')
      .html(image.keywords.join(', '));

    $(template)
      .find('h3')
      .html(image.description);

    $(template)
      .find('.modal-body')
      .empty()
      .append(thumbWrapper)
      .append(keywords);

    $(template)
      .find('#purchase')
      .data('id', image.id);

    return template;
  }

  function renderSubscriptionChoices(template, subscriptions) {
    var subscriptionForm = $('<form>');
    var subscriptionSelect = $('<select>');
    var submitButton = $('<button>');

    $.each(subscriptions, function (i, subscription) {
      subscriptionSelect.append(new Option(subscription.name, subscription.id));
    });

    subscriptionSelect.attr('name', 'subscription');

    $(submitButton)
      .attr('id', 'submit-subs')
      .attr('name', 'submit')
      .attr('type', 'submit')
      .addClass('btn btn-primary pull-right')
      .html('Select <i class="fa fa-shopping-bag"></i>');

    $(subscriptionForm)
      .attr('method', 'get')
      .attr('id', 'subscription-form')
      .append(subscriptionSelect)
      .append(submitButton);

    $(template)
      .find('h3')
      .html('Select a Subscription');

    $(template)
      .find('.modal-body')
      .empty()
      .append(subscriptionForm);

    return template;
  }
});
