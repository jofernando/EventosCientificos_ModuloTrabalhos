@extends('layouts.app')

@section('content')
    <div id="error-bricks" class="alert alert-danger"></div>
    <div id="paymentBrick_container">
    </div>
@endsection

@section('javascript')
    @parent
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const key = @json($key);
        const mp = new MercadoPago(key, {
            locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();
        const categoria = @json($categoria);
        const user = @json($user);
        const inscricao = @json($inscricao);
        const evento = @json($evento);
        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    /*
                        "amount" é a quantia total a pagar por todos os meios de pagamento com exceção da Conta Mercado Pago e Parcelas sem cartão de crédito, que têm seus valores de processamento determinados no backend através do "preferenceId"
                    */
                    amount: categoria.valor_total,
                    payer: {
                        firstName: user.name.split(' ').slice(0, -1).join(" "),
                        lastName: user.name.split(' ').pop(),
                        identification: {
                            "type": "CPF",
                            "number": user.cpf,
                        },
                        email: user.email,
                        address: {
                            zipCode: user.endereco.cep,
                            federalUnit: user.endereco.uf,
                            city: user.endereco.cidade,
                            neighborhood: user.endereco.bairro,
                            streetName: user.endereco.rua,
                            streetNumber: user.endereco.numero,
                            complement: user.endereco.complemento,
                        },
                    },
                },
                customization: {
                    visual: {
                        style: {
                            customVariables: {
                                "baseColor": "#114048",
                            },
                            theme: "bootstrap",
                        },
                    },
                    paymentMethods: {
                        atm: "all",
                        creditCard: "all",
                        bankTransfer: "all",
                        ticket: "all"
                    },
                    installments: 1,
                },
                callbacks: {
                    onReady: () => {
                        /*
                        Callback chamado quando o Brick está pronto.
                        Aqui, você pode ocultar seu site, por exemplo.
                        */
                    },
                    onSubmit: ({ selectedPaymentMethod, formData }) => {
                        formData.evento = evento.id;
                        // callback chamado quando há click no botão de envio de dados
                        return new Promise((resolve, reject) => {
                            fetch("/checkout/process_payment", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                },
                                body: JSON.stringify(formData)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erro na requisição.');
                                }
                                return response.json();
                            })
                            .then(response => {
                                if (response.redirect_url) {
                                    // Redirecionar o usuário para a URL fornecida
                                    window.location.href = response.redirect_url;
                                } else {
                                    console.log('URL de redirecionamento não encontrada na resposta.');
                                }
                                resolve();
                            })
                            .catch(error => {
                                var errorDiv = document.getElementById('error-bricks');
                                errorDiv.innerHTML = 'Error na requisição, recarregue a página. Se o error persistir entre em contato com o organizador do evento.'
                                console.error('Erro na requisição:', error);
                                reject();
                            });
                        });
                    },
                    onError: (error) => {
                        // callback chamado para todos os casos de erro do Brick
                        console.log(2);
                        console.error(error);
                    }
                }
            };
            window.paymentBrickController = await bricksBuilder.create(
                "payment",
                "paymentBrick_container",
                settings
            );
        };
        renderPaymentBrick(bricksBuilder);
    </script>
@endsection
