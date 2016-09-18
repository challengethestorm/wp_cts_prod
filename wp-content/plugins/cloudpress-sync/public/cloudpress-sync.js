(function($) {

  function CPSync() {
    var oneTimeToken = 0;
    window.getOneTimeToken = function(token) {
      oneTimeToken = token;
    }

    function after_login() {
      window.location.reload();
    }

    this.after_login = after_login;
    
    window.addEventListener("message", receiveMessage, false);

    var self = this;
    function receiveMessage(event) {
      self[event.data.callback]();
    }


    function log(log) {
      $("#progress-message").html(log);
    }

    function progress_log(log) {

    }

    var download_size;
    var upload_size;
    var volumes = 0;
    var progress_value = 0;
    var progress_total = 1;
    var progressbar = jQuery("#progress-value");

    function progress(val) {
      if (val) {
        progress_value += val;
      } else {
        progress_value++;
      }

      //console.log(progress_value + "##" + progress_total + "##" + val);
      var per = Math.round((progress_value / progress_total) * 100);
      if (per > 100) {
        per = 99;
      }
      progressbar.css("width", per + "%");
      jQuery('#progress-percent').html(per + '%');
    }


    function reset_progress(){
      progress_total = 0;
      progress_value = 0;
      progressbar.css("width", "0%");
      jQuery('#progress-percent').html('0%');
    }
    

    function syncCall(action, data, callback, error_callback) {
      if (!error_callback) {
        error_callback = function(error) {
          console.error(error);
        }
      }

      var post_data = {
        'action': action,
        'oneTimeToken' : oneTimeToken,
        'cp_sync_nounce' : cloudpress_nounce,
        'credentials' : auth_data
      };

      if ($.type(data) === "string") {
        post_data['step'] = data;
      } else {
        post_data = $.extend(post_data, data);
      }

      $.ajax({
          type: "POST",
          dataType : "json",
          url: ajaxurl,
          data : post_data,
          success : function(response) {
            if (!response) response = {};
            callback(response); 
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
             error_callback(textStatus, errorThrown)
          }
      });
    }

    function pull() {
      reset_progress();
      showProgress();
      var result = Promise.resolve(1);
      for (var i = 0; i < steps['pull'].length; i++) {
        progress_total++;
        (function (step) {
          result = result.then(function(step_result, progress_add) {
            return new Promise(function(resolve, reject) {
              step['action'](step, function(progress_add) {
                progress(progress_add);
                resolve();
              }, reject, step_result, result);
            })
          }); 
        })(steps['pull'][i]);
      };

     result.then(function() {
      log("Transfer operation finished.");
      $("#cp_sync_log").html("");
      $("#cp_sync_log").addClass('empty');
      $("#cloudpress-sync-normal-text").show();
      $("#cloudpress-sync-installing-text").hide();
      $("#cloudpress-sync-rollback").removeClass('disabled');
      $(".cloudpress-sync-general-overlay").each(function() {
        if (!$(this).parent().hasClass('disabled'))
          $(this).hide();
      });
      $("#cloudpress-sync-installing-animation-wrapper").hide();
      $("#cloudpress-sync-notification-wrapper p").html('<strong>Your site has been successfully updated.</strong> A backup of the previous version has been created. You can roll back to that version using the Rollback button below.');
      $("#cloudpress-sync-notification-wrapper").show();
      }).catch(function(error) {
          log(error);
      });
    }

    function rollback(first) {
      reset_progress();
      showProgress();
      progress_total++;
      if (first) {
        log("Rolback from first backup started.");
        syncCall("cloudpress_rollback_first", "", function() {
          log("Rolback from first backup finished.");
          progress();
          hideProgress();
        })
      } else {
        log("Rolback started.");
        syncCall("cloudpress_rollback", "", function() {
          log("Rolback finished.");
          progress();
          hideProgress();
        })
      }
    }

    function push(){
      reset_progress();
      showProgress();
      var result = Promise.resolve(1);
      for (var i = 0; i < steps['push'].length; i++) {
        progress_total++;
        (function (step) {
          result = result.then(function(step_result, progress_add) {
            return new Promise(function(resolve, reject) {
                step['action'](step, function() {
                  progress(progress_add);
                  resolve();
                }, reject, step_result);
            })
          }); 
        })(steps['push'][i]);
      };

     result.then(function() {
        log("Transfer operation finished.");
        $("#cp_sync_log").html("");
        $("#cp_sync_log").addClass('empty');
        $("#cloudpress-sync-normal-text").show();
        $("#cloudpress-sync-installing-text").hide();
        $(".cloudpress-sync-general-overlay").each(function() {
          if (!$(this).parent().hasClass('disabled'))
            $(this).hide();
        });
        $("#cloudpress-sync-installing-animation-wrapper").hide();
        $("#cloudpress-sync-notification-wrapper p").html('<strong>Your CloudPress project has been successfully updated.</strong>');
        $("#cloudpress-sync-notification-wrapper").show();
        }).catch(function(error) {
            log(error);
        });
    }


    function showProgress() {
      $("#cloudpress-sync-normal-text").hide();
      $("#cloudpress-sync-installing-text").show();
      $(".cloudpress-sync-general-overlay").show();
      $("#cloudpress-sync-installing-animation-wrapper").show();
    }

    function hideProgress() {
      $("#cloudpress-sync-normal-text").show();
      $("#cloudpress-sync-installing-text").hide();
      $(".cloudpress-sync-general-overlay").hide();
      $("#cloudpress-sync-installing-animation-wrapper").hide();
    }


    var steps = {
      "pull" : [
        {
          name : "presetup",
          action : function(step, resolve, reject, result) {
            syncCall("cloudpress_pull", step.name, function(response) {
              if (response.error){
                reject(response.error);
              } else {
                resolve(0.1);
              }
             }, function() {
              reject("Call failed, stopping operation.");
            });
          }
        },
        {
          name : "required",
          action : function(step, resolve, reject, result) {
            log("Getting sync data from CloudPress.");
            syncCall("cloudpress_pull", step.name, function(response) {
              if (response.error){
                reject(response.error);
              } else {
                download_size = response.download_size;
                volumes = response.volumes;
                progress_total += volumes * 1.2;
                if (response.needs_sync === true) {
                  resolve();
                } else {
                  reject('Sync is not required.');
                }
              }
             }, function() {
              reject("Call failed, stopping operation.");
            });
          }
        },
        {
          name : "requirements",
          action : function(step, resolve, reject, result) {
            log("Checking requirements");
            syncCall("cloudpress_pull", step.name, function(response) {
                if (response.error){
                   reject(response.error);
                } else {
                  if (response.can_sync === true) {
                    resolve();
                  } else {
                    reject('Requirements check failed.');
                  }
                }
            }, function() {
              reject("Call failed, stopping operation.");
            });
          }
        },
        
        {
          name : "download",
          action : function(step, resolve, reject, result) {
            log("Downloading site data from CloudPress. Download size (" + download_size + ").");
            var active_volume = 0;
            var last_volume;
            var retry = 0;
            function downloadVolume(volume_id) {
              active_volume++;
              last_volume = volume_id;
              if (volume_id > 0) {
                progress();
              }
              log("Downloading volume no. " + active_volume + ".");
              syncCall("cloudpress_pull", {step : "download_volume", volume_id : volume_id}, function(response) {
                if (response.success === true) {
                  if (response.next_volume) {
                    downloadVolume(response.next_volume)
                  } else {
                    resolve();
                  }
                } else {
                  if (retry < 5) {
                    retry++;
                    downloadVolume(last_volume);
                  } else {
                    reject("download failed, stopping operation. (error:" + response.error + ")");
                  }
                }
              }, function() {
                  if (retry < 5) {
                    retry++;
                    downloadVolume(last_volume);
                  } else {
                    reject("download failed, stopping operation. (error: call failed)");
                  }
              });
            }

            downloadVolume(0)
          }
        },

        {
          name : "backup",
          action : function(step, resolve, reject, result) {
            log("Backing up local files and database.");

            var retry = 0;
            function backup() {
              syncCall("cloudpress_pull", {'step' : step.name, 'retry' : retry}, function(response) {
                if (response && response.success === true) { 
                  resolve();
                } else {
                  retry++;
                  if (retry < 20) {
                    backup();
                  } else {
                    reject("backup call failed, stopping sync.");
                  }
                }
              }, function() {
                  retry++;
                  if (retry < 20) {
                    backup();
                  } else {
                    reject("backup call failed, stopping sync.");
                  }
              });
            }

            backup();

          }
        },

         {
          name : "replace",
          action : function(step, resolve, reject, result) {
            log("Replacing site files and database.");
            syncCall("cloudpress_pull", step.name, function(response) {
              if (response.error) {  
                reject(response.error);
              } else {
                resolve();
              }
            }, function() {
              reject("Call failed, stopping operation.");
            });
          }
        }
      ],
      "push" :[
        {
          name : "required",
          action : function(step, resolve, reject, result) {
            log("Getting sync data from CloudPress.");
            syncCall("cloudpress_push", step.name, function(response) {
              if (response.error) {  
                reject(response.error);
              } else {
                  if (response.needs_sync === true) {
                    upload_size = response.upload_size;
                    progress_total += parseFloat(response.upload_size) * 1.2;
                    resolve(0.1);
                  } else {
                   reject("Sync not required.");
                  }
              }
             }, function() {
              reject("Call failed, stopping operation.");
            });
          }
        },
        /*
        {
          name : "backup",
          action : function(step, resolve, reject, result) {
            log("Backup cloudpress files and database.");
            setTimeout(function() {
              log("Backup completed, continue.");
              resolve();
            }, 100);
          }
        },
*/

        {
          name : "upload",
          action : function(step, resolve, reject, result) {
            log("Sending latest changes to CloudPress. Upload size (" + upload_size + ").");
            var active_volume = 0;
            function uploadVolume(volume_id) {
              active_volume++;

              if (volume_id > 0) {
                progress();
              }

              log("Uploading volume no. " + active_volume + ".");
              syncCall("cloudpress_push", {step : "upload_volume", volume_id : volume_id}, function(response) {
                if (response.success === true) {
                  if (response.next_volume) {
                    uploadVolume(response.next_volume)
                  } else {
                    resolve();
                  }
                } else {
                  reject("upload failed, stopping operation.");
                }
              }, function() {
                reject("Call failed, stopping operation.");
              });
            }

            uploadVolume(0);
          }
        }
      ]
    }

    $(document).ready(function() {
      if (oneTimeToken == -1) {
         $('#cloudpress-sync-not-loggedin').show();
         alert("This project doesn't belong to the user you are currently logged in to cloud-press.net. Please login with the right user and refresh this page.");
         return;
      }
      if (oneTimeToken) {
        $('#cloudpress-sync-loggedin').show();
    
        $("#pull-project").click(function() {
        $("#cloudpress-sync-popup-wrapper").show();
        $("#cloudpress-sync-popup-wrapper").animate({opacity: '1'}, 300);
        $("#cloudpress-sync-notification-wrapper5").hide();
        });
        
        $("#pull-project2").click(function() {
          $("#pull-project").trigger('click');
        });
        
        $("#pull-project-ok").click(function() {
          $("#cloudpress-sync-popup-wrapper").animate({opacity: '0'}, 300, function() {
           $("#cloudpress-sync-popup-wrapper").hide();
          });

          pull();
        });
        
        $("#pull-project-cancel").click(function() {
            $("#cloudpress-sync-popup-wrapper").animate({opacity: '0'}, 300, function() {
                $("#cloudpress-sync-popup-wrapper").hide();
            });
        });

        $("#push-project").click(function() {
          push();
        });

        $("#rollback-project").click(function() {
          rollback();
        });

        $("#rollback-first-project").click(function() {
          rollback(true);
        });
      } else {

        $('#cloudpress-sync-not-loggedin').show();
        
        var loginLink = "//" + cloudpress_server + "/login?callback=after_login&TB_iframe=true&height=500&width=800";
        $('#cloudpress-sync-not-loggedin').show();
        $('#cloudpress-sync-not-loggedin').find('#loggin-link').attr("href", loginLink);
        tb_show("You need to login to cloud-press.net",loginLink, "");
      }
    });
  }
  window.cpsync = new CPSync();
})(jQuery);