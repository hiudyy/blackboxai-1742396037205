document.addEventListener('DOMContentLoaded', function() {
    const telefoneInput = document.querySelector('input[name="telefone"]');
    const telefoneFeedback = document.createElement('div');
    telefoneFeedback.className = 'invalid-feedback';
    telefoneFeedback.textContent = 'Digite um número de telefone válido com DDD.';
    
    if (telefoneInput) {
        telefoneInput.parentNode.appendChild(telefoneFeedback);
        
        telefoneInput.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            
            if (valor.length > 11) {
                valor = valor.slice(0, 11);
            }
            
            if (valor.length > 2) {
                valor = '(' + valor.slice(0, 2) + ') ' + valor.slice(2);
            }
            
            if (valor.length > 10) {
                valor = valor.slice(0, 10) + '-' + valor.slice(10);
            }
            
            e.target.value = valor;
            
            if (valor.replace(/\D/g, '').length !== 11) {
                e.target.classList.add('is-invalid');
                telefoneFeedback.style.display = 'block';
            } else {
                e.target.classList.remove('is-invalid');
                telefoneFeedback.style.display = 'none';
            }
        });
    }

    const nomeInput = document.querySelector('input[name="nome"]');
    const nomeFeedback = document.createElement('div');
    nomeFeedback.className = 'invalid-feedback';
    nomeFeedback.textContent = 'Digite seu nome completo.';
    
    if (nomeInput) {
        nomeInput.parentNode.appendChild(nomeFeedback);
        
        // Função para capitalizar a primeira letra de cada palavra
        function capitalizarNome(texto) {
            return texto.toLowerCase().split(' ').map(palavra => {
                if (palavra.length > 0) {
                    return palavra.charAt(0).toUpperCase() + palavra.slice(1);
                }
                return palavra;
            }).join(' ');
        }
        
        nomeInput.addEventListener('input', function(e) {
            // Mantém a posição do cursor
            const cursorPos = e.target.selectionStart;
            const textoAntigo = e.target.value;
            const textoNovo = capitalizarNome(textoAntigo);
            
            // Só atualiza se houver mudança para evitar loop
            if (textoAntigo !== textoNovo) {
                e.target.value = textoNovo;
                // Restaura a posição do cursor
                e.target.setSelectionRange(cursorPos, cursorPos);
            }
            
            // Validação original
            if (e.target.value.trim().length === 0) {
                e.target.classList.add('is-invalid');
                nomeFeedback.style.display = 'block';
            } else {
                e.target.classList.remove('is-invalid');
                nomeFeedback.style.display = 'none';
            }
        });
        
        // Capitaliza também quando o campo perde o foco
        nomeInput.addEventListener('blur', function(e) {
            e.target.value = capitalizarNome(e.target.value);
        });
    }

    const usuarioInput = document.querySelector('input[name="usuario"]');
    const usuarioFeedback = document.createElement('div');
    usuarioFeedback.className = 'invalid-feedback';
    usuarioFeedback.textContent = 'O nome de usuário não pode conter espaços, acentos ou caracteres especiais.';
    
    if (usuarioInput) {
        usuarioInput.parentNode.appendChild(usuarioFeedback);
        
        let timeoutId;
        
        usuarioInput.addEventListener('input', function(e) {
            const valor = e.target.value;
            
            // Validação de caracteres especiais
            if (/\s/.test(valor) || /[áàâãäéèêëíìîïóòôõöúùûüçÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ]/.test(valor) || /[^a-zA-Z0-9]/.test(valor)) {
                e.target.classList.add('is-invalid');
                usuarioFeedback.textContent = 'O nome de usuário não pode conter espaços, acentos ou caracteres especiais.';
                usuarioFeedback.style.display = 'block';
                return;
            }
            
            // Limpa o timeout anterior se existir
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Configura um novo timeout para verificar o usuário
            timeoutId = setTimeout(() => {
                if (valor.length > 0) {
                    fetch(`cadastro.php?verificar_usuario=${encodeURIComponent(valor)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.existe) {
                                e.target.classList.add('is-invalid');
                                usuarioFeedback.textContent = 'Este nome de usuário já está em uso.';
                                usuarioFeedback.style.display = 'block';
                            } else {
                                e.target.classList.remove('is-invalid');
                                usuarioFeedback.style.display = 'none';
                            }
                        });
                } else {
                    e.target.classList.remove('is-invalid');
                    usuarioFeedback.style.display = 'none';
                }
            }, 500); // Espera 500ms após o usuário parar de digitar
        });
    }

    const senhaInput = document.querySelector('input[name="senha"]');
    const senhaFeedback = document.createElement('div');
    senhaFeedback.className = 'invalid-feedback';
    senhaFeedback.textContent = 'A senha deve ter pelo menos 6 caracteres.';
    
    const confirmarSenhaInput = document.querySelector('input[name="confirmar_senha"]');
    const confirmarSenhaFeedback = document.createElement('div');
    confirmarSenhaFeedback.className = 'invalid-feedback';
    confirmarSenhaFeedback.textContent = 'As senhas não coincidem.';

    if (senhaInput && confirmarSenhaInput) {
        senhaInput.parentNode.appendChild(senhaFeedback);
        confirmarSenhaInput.parentNode.appendChild(confirmarSenhaFeedback);

        function validarSenhas() {
            if (senhaInput.value.length < 6) {
                senhaInput.classList.add('is-invalid');
                senhaFeedback.style.display = 'block';
            } else {
                senhaInput.classList.remove('is-invalid');
                senhaFeedback.style.display = 'none';
            }

            if (confirmarSenhaInput.value && senhaInput.value !== confirmarSenhaInput.value) {
                confirmarSenhaInput.classList.add('is-invalid');
                confirmarSenhaFeedback.style.display = 'block';
            } else {
                confirmarSenhaInput.classList.remove('is-invalid');
                confirmarSenhaFeedback.style.display = 'none';
            }
        }

        senhaInput.addEventListener('input', validarSenhas);
        confirmarSenhaInput.addEventListener('input', validarSenhas);
    }

    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar nome
            if (nomeInput.value.trim().length === 0) {
                nomeInput.classList.add('is-invalid');
                nomeFeedback.style.display = 'block';
                return;
            }
            
            // Validar telefone
            if (telefoneInput.value.replace(/\D/g, '').length !== 11) {
                telefoneInput.classList.add('is-invalid');
                telefoneFeedback.style.display = 'block';
                return;
            }
            
            // Validar usuário
            const valorUsuario = usuarioInput.value;
            if (/\s/.test(valorUsuario) || /[áàâãäéèêëíìîïóòôõöúùûüçÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ]/.test(valorUsuario) || /[^a-zA-Z0-9]/.test(valorUsuario)) {
                usuarioInput.classList.add('is-invalid');
                usuarioFeedback.style.display = 'block';
                return;
            }
            
            // Validar senhas
            if (senhaInput.value.length < 6) {
                senhaInput.classList.add('is-invalid');
                senhaFeedback.style.display = 'block';
                return;
            }
            
            if (senhaInput.value !== confirmarSenhaInput.value) {
                confirmarSenhaInput.classList.add('is-invalid');
                confirmarSenhaFeedback.style.display = 'block';
                return;
            }
            
            // Preencher o modal com os dados
            document.getElementById('confirmaNome').textContent = nomeInput.value;
            document.getElementById('confirmaTelefone').textContent = telefoneInput.value;
            document.getElementById('confirmaUsuario').textContent = usuarioInput.value;
            
            // Mostrar o modal
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            modal.show();
        });
    }

    // Evento de confirmação do cadastro
    const btnConfirmarCadastro = document.getElementById('btnConfirmarCadastro');
    if (btnConfirmarCadastro) {
        btnConfirmarCadastro.addEventListener('click', function() {
            form.submit();
        });
    }

    // Função para formatar o telefone
    function formatarTelefone(input) {
        let valor = input.value.replace(/\D/g, '');
        if (valor.length > 0) {
            valor = '(' + valor;
            if (valor.length > 3) {
                valor = valor.slice(0, 3) + ') ' + valor.slice(3);
            }
            if (valor.length > 9) {
                valor = valor.slice(0, 9) + '-' + valor.slice(9);
            }
            if (valor.length > 15) {
                valor = valor.slice(0, 15);
            }
        }
        input.value = valor;
    }

    // Função para validar o formulário
    function validarFormulario() {
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        const usuario = document.getElementById('usuario').value;
        
        if (senha.length < 6) {
            alert('A senha deve ter pelo menos 6 caracteres.');
            return false;
        }
        
        if (senha !== confirmarSenha) {
            alert('As senhas não coincidem.');
            return false;
        }
        
        if (/[^a-zA-Z0-9]/.test(usuario)) {
            alert('O nome de usuário não pode conter espaços ou caracteres especiais.');
            return false;
        }
        
        return true;
    }

    // Mostrar/ocultar senhas
    const mostrarSenhasCheckbox = document.getElementById('mostrarSenhas');
    if (mostrarSenhasCheckbox) {
        mostrarSenhasCheckbox.addEventListener('change', function() {
            const senhaInput = document.getElementById('senha');
            const confirmarSenhaInput = document.getElementById('confirmar_senha');
            
            const tipo = this.checked ? 'text' : 'password';
            senhaInput.type = tipo;
            confirmarSenhaInput.type = tipo;
        });
    }

    // Autocomplete para busca de clientes
    const clienteInput = document.getElementById('cliente_busca');
    const clienteIdInput = document.getElementById('cliente_id');
    const clienteListaDiv = document.getElementById('cliente_lista');
    
    if (clienteInput && clienteIdInput && clienteListaDiv) {
        console.log('Elementos de autocomplete encontrados:', { 
            clienteInput: clienteInput.id, 
            clienteIdInput: clienteIdInput.id, 
            clienteListaDiv: clienteListaDiv.id 
        });
        
        // Estilização do container de resultados
        clienteListaDiv.style.position = 'absolute';
        clienteListaDiv.style.width = '100%';
        clienteListaDiv.style.maxHeight = '200px';
        clienteListaDiv.style.overflowY = 'auto';
        clienteListaDiv.style.backgroundColor = '#2b3035';
        clienteListaDiv.style.border = '1px solid #495057';
        clienteListaDiv.style.borderRadius = '0.25rem';
        clienteListaDiv.style.zIndex = '1000';
        clienteListaDiv.style.display = 'none';
        
        let timeoutId;
        
        clienteInput.addEventListener('input', function() {
            const termo = this.value.trim();
            console.log('Evento input acionado. Termo:', termo);
            
            // Limpa o timeout anterior
            clearTimeout(timeoutId);
            
            // Se o campo estiver vazio, limpa os resultados
            if (termo.length === 0) {
                clienteListaDiv.innerHTML = '';
                clienteListaDiv.style.display = 'none';
                clienteIdInput.value = '';
                return;
            }
            
            // Define um timeout para evitar muitas requisições
            timeoutId = setTimeout(function() {
                console.log('Fazendo requisição para buscar_cliente.php com termo:', termo);
                // Faz a requisição para buscar os clientes
                fetch(`buscar_cliente.php?termo=${encodeURIComponent(termo)}`)
                    .then(response => {
                        console.log('Resposta recebida:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Dados recebidos:', data);
                        if (data.success) {
                            clienteListaDiv.innerHTML = '';
                            
                            if (data.clientes.length === 0) {
                                console.log('Nenhum cliente encontrado');
                                clienteListaDiv.style.display = 'none';
                                return;
                            }
                            
                            console.log('Clientes encontrados:', data.clientes.length);
                            data.clientes.forEach(cliente => {
                                const item = document.createElement('div');
                                item.className = 'p-2 cliente-item';
                                item.style.cursor = 'pointer';
                                item.style.borderBottom = '1px solid #495057';
                                item.style.color = '#ffffff';
                                item.innerHTML = `<strong>${cliente.nome}</strong> - Tel: ${cliente.telefone}`;
                                
                                item.addEventListener('mouseover', function() {
                                    this.style.backgroundColor = '#343a40';
                                });
                                
                                item.addEventListener('mouseout', function() {
                                    this.style.backgroundColor = 'transparent';
                                });
                                
                                item.addEventListener('click', function() {
                                    clienteInput.value = cliente.nome;
                                    clienteIdInput.value = cliente.id;
                                    clienteListaDiv.style.display = 'none';
                                });
                                
                                clienteListaDiv.appendChild(item);
                            });
                            
                            clienteListaDiv.style.display = 'block';
                        } else {
                            console.error('Erro na resposta:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar clientes:', error);
                    });
            }, 300); // 300ms de delay
        });
        
        // Fecha a lista quando clicar fora
        document.addEventListener('click', function(e) {
            if (e.target !== clienteInput && e.target !== clienteListaDiv) {
                clienteListaDiv.style.display = 'none';
            }
        });
    }

    // Configuração da Chave PIX
    const modalChavePix = document.getElementById('modalChavePix');
    if (modalChavePix) {
        // Carregar dados da chave PIX quando o modal for aberto
        modalChavePix.addEventListener('show.bs.modal', function() {
            fetch('carregar_chave_pix.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('chavePix').value = data.data.chavePix;
                        document.getElementById('tipoPix').value = data.data.tipoPix;
                        document.getElementById('nomePix').value = data.data.nomePix;
                    } else {
                        document.getElementById('chavePix').value = '';
                        document.getElementById('tipoPix').value = 'cpf';
                        document.getElementById('nomePix').value = '';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar chave PIX:', error);
                });
        });
        
        // Salvar a chave PIX
        document.getElementById('btnSalvarChavePix').addEventListener('click', function() {
            const chavePix = document.getElementById('chavePix').value.trim();
            const tipoPix = document.getElementById('tipoPix').value;
            const nomePix = document.getElementById('nomePix').value.trim();
            
            if (!chavePix || !nomePix) {
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            const dados = {
                chavePix: chavePix,
                tipoPix: tipoPix,
                nomePix: nomePix
            };
            
            fetch('salvar_chave_pix.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza os botões de WhatsApp com a nova chave PIX
                    // Importante: chamar isso antes de qualquer outra ação
                    if (data.data) {
                        atualizarBotoesWhatsApp(data.data);
                    }
                    
                    alert('Chave PIX configurada com sucesso!');
                    bootstrap.Modal.getInstance(modalChavePix).hide();
                    
                    // Verifica se o servidor solicitou um redirecionamento ou recarregamento
                    if (data.reload) {
                        // Pequeno atraso para garantir que a atualização dos botões seja concluída
                        setTimeout(() => {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url; // Redireciona para a URL especificada
                            } else {
                                location.reload(); // Recarrega a página atual
                            }
                        }, 500);
                    }
                } else {
                    alert('Erro ao configurar chave PIX: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao salvar chave PIX:', error);
                alert('Erro ao salvar a chave PIX. Tente novamente.');
            });
        });
    }

    // Função para atualizar os botões de WhatsApp com a nova chave PIX
    function atualizarBotoesWhatsApp(dadosPix) {
        console.log('Atualizando botões WhatsApp com dados:', dadosPix);
        
        // Obtém todos os botões de WhatsApp na página
        const botoesWhatsApp = document.querySelectorAll('a[href^="https://wa.me/"]');
        console.log('Botões encontrados:', botoesWhatsApp.length);
        
        if (botoesWhatsApp.length === 0) {
            console.log('Nenhum botão de WhatsApp encontrado na página');
            return;
        }
        
        botoesWhatsApp.forEach((botao, index) => {
            // Obtém a URL atual do botão
            const urlAtual = botao.getAttribute('href');
            console.log(`Botão ${index+1} - URL atual:`, urlAtual);
            
            // Extrai o número de telefone e o texto atual
            const match = urlAtual.match(/https:\/\/wa\.me\/(\d+)\?text=(.+)/);
            if (!match) {
                console.log(`Botão ${index+1} - Formato de URL não reconhecido`);
                return;
            }
            
            const telefone = match[1];
            let mensagem = decodeURIComponent(match[2]);
            console.log(`Botão ${index+1} - Mensagem decodificada:`, mensagem);
            
            try {
                // Divide a mensagem em partes para identificar a parte da chave PIX
                const partesMensagem = mensagem.split('\n\n');
                
                // Remove a última parte se contiver informações de PIX
                if (partesMensagem.length > 1 && partesMensagem[partesMensagem.length - 1].includes('*Chave PIX')) {
                    partesMensagem.pop(); // Remove a última parte (informações de PIX)
                }
                
                // Reconstrói a mensagem sem a parte da chave PIX
                let mensagemSemPix = partesMensagem.join('\n\n');
                console.log(`Botão ${index+1} - Mensagem sem PIX:`, mensagemSemPix);
                
                // Adiciona a nova informação de PIX
                let novaMensagem = mensagemSemPix;
                if (dadosPix.chavePix) {
                    novaMensagem += `\n\n*Chave PIX ${dadosPix.tipoPix.toUpperCase()}:* ${dadosPix.chavePix}\n${dadosPix.nomePix}`;
                }
                console.log(`Botão ${index+1} - Nova mensagem:`, novaMensagem);
                
                // Atualiza o link do botão
                const novaURL = `https://wa.me/${telefone}?text=${encodeURIComponent(novaMensagem)}`;
                console.log(`Botão ${index+1} - Nova URL:`, novaURL);
                botao.setAttribute('href', novaURL);
            } catch (error) {
                console.error(`Erro ao processar botão ${index+1}:`, error);
            }
        });
        
        console.log('Atualização dos botões de WhatsApp concluída');
    }
}); 