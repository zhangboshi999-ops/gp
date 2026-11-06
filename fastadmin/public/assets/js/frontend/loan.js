define(['jquery', 'bootstrap', 'frontend', 'form'], function ($, undefined, Frontend, Form) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        index: function () {
            //提交成功后的跳转(提示由 Form.api.submit 统一弹出,这里不要再弹一次)
            var success = function (data, ret) {
                setTimeout(function () {
                    location.href = (ret && ret.url) ? ret.url : Config.moduleurl + '/loan/index';
                }, 800);
            };

            //协议展开/关闭
            $(document).on('click', '#agreement-toggle, #agreement-open', function () {
                var body = $('#agreement-body');
                body.toggleClass('ln-open');
                $('#agreement-toggle').text(body.hasClass('ln-open') ? '▲ Yopish' : '▼ Davom etish');
            });

            //勾选同意协议后才可提交申请
            $(document).on('change', '#agree-check', function () {
                $('#apply-submit').prop('disabled', !$(this).prop('checked'));
            });

            //申请表单
            var applyForm = $("#apply-form");
            if (applyForm.length > 0) {
                applyForm.data("validator-options", validatoroptions);
                Form.api.bindevent(applyForm, success, null, function () {
                    if (!$('#agree-check').prop('checked')) {
                        Layer.msg("Avval shartnomani o'qib, rozilik bering");
                        return false;
                    }
                    return true;
                });
            }

            //银行卡表单
            var bankForm = $("#bank-form");
            if (bankForm.length > 0) {
                bankForm.data("validator-options", validatoroptions);
                Form.api.bindevent(bankForm, function (data, ret) {
                    setTimeout(function () {
                        location.reload();
                    }, 800);
                });
            }

            //提款表单
            var withdrawForm = $("#withdraw-form");
            if (withdrawForm.length > 0) {
                withdrawForm.data("validator-options", validatoroptions);
                Form.api.bindevent(withdrawForm, success);

                //还款计划试算
                var termSelect = $("#c-term");
                var loadPlan = function () {
                    var url = termSelect.data('url');
                    //提款处理中时已锁定还款计划，无需再试算
                    if (!url || termSelect.prop('disabled')) {
                        return;
                    }
                    Fast.api.ajax({url: url, data: {term: termSelect.val()}}, function (data, ret) {
                        var plan = ret.data || {};
                        $("#plan-box").find("[data-plan=payment]").text(plan.payment || '--');
                        $("#plan-box").find("[data-plan=interest]").text(plan.interest || '--');
                        $("#plan-box").find("[data-plan=total]").text(plan.total || '--');
                        return false;
                    }, function () {
                        return false;
                    });
                };
                termSelect.on('change', loadPlan);
                loadPlan();
            }
        },
        repayment: function () {
            //图片地址补全
            var toUrl = function (url) {
                try {
                    return Config.upload ? Fast.api.cdnurl(url) : url;
                } catch (e) {
                    return url;
                }
            };
            var input = $("#c-images");
            var preview = $("#voucher-preview");
            var tip = $("#voucher-tip");
            var urls = $.trim(input.val()) ? input.val().split(",") : [];

            var sync = function () {
                input.val(urls.join(","));
                tip.text(urls.length ? "Yuklandi " + urls.length + " ta hujjat" : "Avval yuqoridagi hisobga pul o'tkazing, so'ng to'lov hujjati rasmini yuklang");
            };
            sync();

            //选择图片后立即上传到 ajax/upload
            $(document).on("change", "#voucher-file", function () {
                var files = this.files;
                if (!files || !files.length) {
                    return;
                }
                var self = this;
                Array.prototype.forEach.call(files, function (file) {
                    var data = new FormData();
                    data.append("file", file);
                    $.ajax({
                        url: Config.moduleurl + "/ajax/upload",
                        type: "POST",
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function (ret) {
                            if (ret && ret.code && ret.data && ret.data.url) {
                                urls.push(ret.data.url);
                                preview.append('<a href="' + toUrl(ret.data.url) + '" target="_blank"><img src="' + toUrl(ret.data.url) + '" alt="O\'tkazma kvitansiyasi"></a>');
                                sync();
                            } else {
                                Layer.msg((ret && ret.msg) ? ret.msg : "Yuklashda xatolik, qayta urinib ko'ring");
                            }
                        },
                        error: function () {
                            Layer.msg("Yuklashda xatolik, qayta urinib ko'ring");
                        }
                    });
                });
                $(self).val("");
            });

            //提交确认还款
            Form.api.bindevent($("#repayment-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = (ret && ret.url) ? ret.url : Config.moduleurl + "/loan/index";
                }, 800);
            }, null, function () {
                if (!urls.length) {
                    Layer.msg("Avval to'lov hujjatini yuklang");
                    return false;
                }
                return true;
            });
        }
    };
    return Controller;
});
