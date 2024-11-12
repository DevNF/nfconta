<?php
namespace NFHub\NFConta;

use Exception;
use NFHub\Common\Tools as ToolsBase;
use CURLFile;

/**
 * Classe Tools
 *
 * Classe responsável pela implementação com a API de boletos do NFHub
 *
 * @category  NFHub
 * @package   NFHub\NFConta\Tools
 * @author    Jefferson Moreira <jeematheus at gmail dot com>
 * @copyright 2020 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools extends ToolsBase
{

    /**
     * Verifica se a NFConta está ativa no NFHub para a empresa
     *
     * @param int $company_id ID da empresa a ser verificada
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    function verificaConta(int $company_id, array $params = []) :array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/nfconta/is_active', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por ativa a NFConta no NFHub
     *
     * @param int $company_id ID da empresa a ter a conta ativada
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function ativaConta(int $company_id, array $params = []): array
    {
        try {
            $dados = $this->post("companies/$company_id/nfconta/create", [], $params);

            return $dados;
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por enviar os documentos para o NFHub
     *
     * @param int $company_id ID da empresa que irá enviar os documentos
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function enviaDocumentos(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['documents']) || empty($dados['documents']) || !is_array($dados['documents'])) {
            $errors[] = 'Os documentos devem ser enviados em um campo chamado documents, e o mesmo deve ser um array';
        } else {
            foreach ($dados['documents'] as $key => $document) {
                if (!isset($dados['documents'][$key]['type'])) {
                    $errors[] = "O documento da posição $key não possuí o campo type";
                } else if (!in_array($dados['documents'][$key]['type'], ['SOCIAL_CONTRACT', 'IDENTIFICATION', 'ENTREPRENEUR_REQUIREMENT', 'MINUTES_OF_CONSTITUTION', 'MINUTES_OF_ELECTION', 'ALLOW_BANK_ACCOUNT_DEPOSIT_STATEMENT', 'IDENTIFICATION_ACCOUNT_OWNER'])) {
                    $errors[] = "O campo type do documento da posição $key deve ter um dos seguintes valores: SOCIAL_CONTRACT, IDENTIFICATION, ENTREPRENEUR_REQUIREMENT, MINUTES_OF_CONSTITUTION, MINUTES_OF_ELECTION, ALLOW_BANK_ACCOUNT_DEPOSIT_STATEMENT, IDENTIFICATION_ACCOUNT_OWNER";
                }

                if (!isset($dados['documents'][$key]['type'])) {
                    $errors[] = "O documento da posição $key não possuí o campo document";
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            foreach ($dados['documents'] as $key => $document) {
                $dados["documents[$key][type]"] = $document['type'];
                $dados["documents[$key][document]"] = $document['document'];
            }

            unset($dados['documents']);

            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/documents", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar os documentos necessários da NFCont
     *
     * @param int $company_id ID da empresa a ter os documentos consultados
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaDocumentos(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get("nfconta/documents", $params);

            return $dados;
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar o saldo da NFConta
     *
     * @param int $company_id ID da empresa a ter o saldo consultado
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaSaldo(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get("nfconta/balance", $params);

            return $dados;
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar o resumo da NFConta
     *
     * @param int $company_id ID da empresa a ter a conta ativada
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function resumo(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get("nfconta/resume", $params);

            return $dados;
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar o extrato da NFConta no NFHub
     *
     * @param int $company_id ID da empresa a ter o extrato retornado
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaExtrato(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/nfconta/extract', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar uma movimentação específica da NFConta no NFHub
     *
     * @param int $company_id ID da empresa no NFHub
     * @param int $extract_id ID da movimentação
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaMovimentacao(int $company_id, int $extract_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get("/nfconta/extract/$extract_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar listar os bancos da NFConta no NFHub
     *
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function listaBancosNFHub(array $params = []): array
    {
        try {
            $dados = $this->get('/nfconta/banks', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar listar as contas bancárias da NFConta no NFHub
     *
     * @param int $company_id ID da empresa no NFHub
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function listaContas(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/nfconta/accounts', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por gerar uma cobrança na NFConta
     *
     * @param int $company_id ID da empresa que irá gerar cobrança
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraCobranca(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['customer_id']) || empty($dados['customer_id'])) {
            $errors[] = 'O ID do cliente é obrigatório';
        }
        if (!isset($dados['value']) || empty($dados['value'])) {
            $errors[] = 'O valor da cobrança é obrigatório';
        }
        if (!isset($dados['due_date']) || empty($dados['due_date'])) {
            $errors[] = 'A data de vencimento da cobrança é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/charges", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por confirmar o recebimento de uma cobrança en dinheiro na NFConta
     *
     * @param int $company_id ID da empresa que irá confirma o recebimento
     * @param int $installment_id ID da cobrança
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function confirmaRecebimentoDinheiro(int $company_id, int $installment_id, array $params = []): array
    {
        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/charges/$installment_id/cash", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por remover uma cobrança na NFConta
     *
     * @param int $company_id ID da empresa que irá remover a cobranca
     * @param int $installment_id ID da cobrança
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function removeCobranca(int $company_id, int $installment_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            $dados = $this->delete("nfconta/charges/$installment_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }


    /**
     * Função resposável por atualizar uma cobrança na NFConta
     *
     * @param int $company_id ID da empresa que irá gerar cobrança
     * @param int $installment_id ID da cobrança no NFHub
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function atualizaCobranca(int $company_id, int $installment_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['value']) || empty($dados['value'])) {
            $errors[] = 'O valor da cobrança é obrigatório';
        }
        if (!isset($dados['due_date']) || empty($dados['due_date'])) {
            $errors[] = 'A data de vencimento da cobrança é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->put("nfconta/charges/$installment_id", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por gerar um depósito na NFConta
     *
     * @param int $company_id ID da empresa que irá gerar depósito
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraDeposito(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['value']) || empty($dados['value'])) {
            $errors[] = 'O valor do depósito é obrigatório';
        }
        if (!isset($dados['due_date']) || empty($dados['due_date'])) {
            $errors[] = 'A data de vencimento do depósito é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/deposit", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por buscar uma cobrança na NFConta
     *
     * @param int $company_id ID da empresa que irá buscar a cobrança
     * @param int $installment_id ID da cobrança
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaCobranca(int $company_id, int $installment_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            $dados = $this->get("installments/$installment_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por gerar uma cobrança de contratação pela NFConta
     *
     * @param array $data dados da cobrança
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraCobrancaContrato(int $company_id, array $dados, bool $signature = false, array $params = []) :array
    {
        $errors = [];
        if (empty($company_id)) {
            $errors[] = 'Não é possível criar uma cobrança sem o ID da empresa';
        }
        if (!isset($dados['customer_id']) || empty($dados['customer_id'])) {
            $errors[] = 'Não é possível criar uma cobrança sem o ID do cliente';
        }
        if (!isset($dados['total_value']) || empty($dados['total_value'])) {
            $errors[] = 'Não é possível criar uma cobrança sem o valor total da mesma';
        }
        if (!isset($dados['due_date']) || empty($dados['due_date'])) {
            $errors[] = 'Não é possível criar uma cobrança sem a dados de vencimento da mesma';
        }
        if (!isset($dados['type']) || empty($dados['type'])) {
            $errors[] = 'Não é possível criar uma cobrança sem o tipo da mesma';
        } else if ($dados['type'] == 2) {
            if (!isset($dados['parcel_value']) || empty($dados['parcel_value'])) {
                $errors[] = 'Não é possível criar uma cobrança por cartão sem o valor da parcela';
            }
            if (!isset($dados['parcel_quantity']) || empty($dados['parcel_quantity'])) {
                $errors[] = 'Não é possível criar uma cobrança por cartão sem a quantidade de parcelas';
            }
            if (!isset($dados['ip']) || empty($dados['ip'])) {
                $errors[] = 'Não é possível criar uma cobrança por cartão sem o ip do cliente';
            }
            if (!isset($dados['credit_card']) || empty($dados['credit_card'])) {
                $errors[] = 'Não é possível criar uma cobrança por cartão sem as informações do mesmo';
            } else {
                if (!isset($dados['credit_card']['name']) || empty($dados['credit_card']['name'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o nome impresso no mesmo';
                }
                if (!isset($dados['credit_card']['number']) || empty($dados['credit_card']['number'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o numero do mesmo';
                }
                if (!isset($dados['credit_card']['expiry_month']) || empty($dados['credit_card']['expiry_month'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o mês de expiração do mesmo';
                }
                if (!isset($dados['credit_card']['expiry_year']) || empty($dados['credit_card']['expiry_year'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o ano de expiração do mesmo';
                }
                if (!isset($dados['credit_card']['ccv']) || empty($dados['credit_card']['ccv'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o ccv do mesmo';
                }
            }
            if (!isset($dados['holder_info']) || empty($dados['holder_info'])) {
                $errors[] = 'Não é possível criar uma cobrança por cartão sem as informações do titular';
            } else {
                if (!isset($dados['holder_info']['name']) || empty($dados['holder_info']['name'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o nome do titular';
                }
                if (!isset($dados['holder_info']['cpfcnpj']) || empty($dados['holder_info']['cpfcnpj'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o CPF/CNPJ do titular';
                }
                if (!isset($dados['holder_info']['cep']) || empty($dados['holder_info']['cep'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o cep do titular';
                }
                if (!isset($dados['holder_info']['address_number']) || empty($dados['holder_info']['address_number'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o número do endereço do titular';
                }
                if (!isset($dados['holder_info']['phone']) || empty($dados['holder_info']['phone'])) {
                    $errors[] = 'Não é possível criar uma cobrança por cartão sem o telefone fixo do titular';
                }
            }
        }

        $dados['company_id'] = $company_id;
        $url = '/nfconta/contracts/charge';
        if ($signature) {
            $url = '/nfconta/contracts/charge/1';
        }
        try {
            $dados = $this->post($url, $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (!isset($dados['body']->message)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por gerar um depósito na NFConta
     *
     * @param int $company_id ID da empresa que irá gerar depósito
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraSecret(int $company_id, array $params = []): array
    {
        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/auth/generate", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar a url do Qrcode de autenticação da NFConta no NFHub
     *
     * @param int $company_id ID da empresa a ter o qrcode retornado
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaUrlQrCode(int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('nfconta/auth/qrcode', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por buscar o boleto de uma cobrança da NFConta no NFHub
     *
     * @param int $company_id ID da empresa a ter o qrcode retornado
     * @param int $installment_id ID da cobrança a ser buscada
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaBoleto(int $company_id, int $installment_id, array $params = []): array
    {
        $this->setDecode(false);
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get("nfconta/charges/$installment_id/pdf", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por retornar as categorias usadas no WFPay
     *
     *
     * @param int $company_id ID da empresa que irá buscar a categoria
     * @param array $category_id ID da categoria
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaCategorias(int $company_id, int $category_id,  array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            if (!empty($category_id)) {
                $params[] = [
                    'name' => 'category_id',
                    'value' => $category_id
                ];
            }

            $dados = $this->get("nfconta/category", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }
            if (!isset($dados['body']->message)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

     /**
     * Função responsável por retornar o Pjbank do WFPay
     *
     * @param int $company_id ID da empresa que irá buscar Pjbank
     * @param int $charge_id ID do WFPay
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaPjbank(int $company_id, int $charge_id,  array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            if (!empty($charge_id)) {
                $params[] = [
                    'name' => 'charge_id',
                    'value' => $charge_id
                ];
            }

            $dados = $this->get("nfconta/pjbank", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }
            if (!isset($dados['body']->message)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

     /**
     * Função responsável por retornar as Transactions do WFPay
     *
     * @param int $company_id ID da empresa que irá buscar Transactions
     * @param int $transaction_id ID do extrato no WFPay
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaTransactions(int $company_id, int $transaction_id,  array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            if (!empty($transaction_id)) {
                $params[] = [
                    'name' => 'transaction_id',
                    'value' => $transaction_id
                ];
            }

            $dados = $this->get("nfconta/transactions", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }
            if (!isset($dados['body']->message)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por simular o pagamento de uma conta
     *
     * @param int $company_id ID da empresa que irá simular o pagamento
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function simulaPagamento(int $company_id, array $dados, array $params = []): array
    {
        if ((!isset($dados['code']) || empty($dados['code'])) && (!isset($dados['bar_code']) || empty($dados['bar_code']))) {
            throw new Exception('Informe a linha digitável ou o código de barras do boleto para poder realizar a simulação', 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/payments/simulate", $dados, $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por realizar um pagamento de uma conta
     *
     * @param int $company_id ID da empresa que irá pagar a conta
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraPagamento(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['code']) || empty($dados['code'])) {
            $errors[] = 'Informe a linha digitável do boleto para poder realizar o pagamento';
        }
        if (!isset($dados['token']) || empty($dados['token'])) {
            $errors[] = 'Informe o token gerado no app do Google Authenticate';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/payments", $dados, $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por realizar um agendamento de um pagamento de uma conta
     *
     * @param int $company_id ID da empresa que irá pagar a conta
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function agendaPagamento(int $company_id, array $dados, array $params = []): array
    {
        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/payments/schedule", $dados, $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por realizar um agendamento de uma transferencia
     *
     * @param int $company_id ID da empresa que irá pagar a conta
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function agendaTransferencia(int $company_id, array $dados, array $params = []): array
    {
        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/transfers/schedule", $dados, $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar um pagamento de uma conta
     *
     * @param int $company_id ID da empresa que irá consultar a conta
     * @param int $historic_id ID do historico no NFHub
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaPagamento(int $company_id, int $historic_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados['company_id'] = $company_id;
            $dados = $this->get("nfconta/payments/$historic_id", $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por realizar uma transferencia
     *
     * @param int $company_id ID da empresa que irá transferir o dinheiro
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function transferir(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if (!isset($dados['value']) || empty($dados['value'])) {
            $errors[] = 'Informe o valor da transferência';
        }
        if (!isset($dados['token']) || empty($dados['token'])) {
            $errors[] = 'Informe o token gerado no app do Google Authenticate';
        }
        if (!isset($dados['bank_id']) || empty($dados['bank_id'])) {
            $errors[] = 'Informe o código do banco';
        }
        if (!isset($dados['name']) || empty($dados['name'])) {
            $errors[] = 'Informe o nome do titular da conta';
        }
        if (!isset($dados['cpfcnpj']) || empty($dados['cpfcnpj'])) {
            $errors[] = 'Informe o CPF/CNPJ do titular a conta';
        }
        if (!isset($dados['agency']) || empty($dados['agency'])) {
            $errors[] = 'Informe o número da agencia';
        }
        if (!isset($dados['account']) || empty($dados['account'])) {
            $errors[] = 'Informe o número da conta bancária';
        }
        if (!isset($dados['account_digit']) || empty($dados['account_digit'])) {
            $errors[] = 'Informe dígito verificador da conta bancária';
        }
        if (!isset($dados['bank_type']) || empty($dados['bank_type'])) {
            $errors[] = 'Informe o tipo de conta bancária';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/transfers", $dados, $params);

            return $dados;
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por gerar uma assinatura no WFPay
     *
     * @param int $company_id ID da empresa que irá gerar cobrança
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraAssinaturaArmazenamento(int $company_id, array $dados, array $params = []): array
    {
        $errors = [];
        if ((!isset($dados['customer_id']) || empty($dados['customer_id'])) && (!isset($dados['customer']) || empty($dados['customer']))) {
            $errors[] = 'O cliente é obrigatório';
        }
        if (!isset($dados['value']) || empty($dados['value'])) {
            $errors[] = 'O valor da assinatura é obrigatório';
        }
        if (!isset($dados['next_due_date']) || empty($dados['next_due_date'])) {
            $errors[] = 'A data de vencimento da assinatura é obrigatório';
        }
        if (!isset($dados['cycle']) || empty($dados['cycle'])) {
            $errors[] = 'O ciclo da assinatura é obrigatório';
        }
        if (!isset($dados['type_payment']) || empty($dados['type_payment'])) {
            $errors[] = 'O tipo de pagamento da assinatura é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados['company_id'] = $company_id;
            $dados = $this->post("nfconta/subscriptions", $dados, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por verificar uma assinatura na WFPay
     *
     * @param int $company_id ID da empresa que irá gerar cobrança
     * @param int $subscription_id ID da assinatura no WFPay
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function verificaStatusArmazenamento(int $company_id, int $subscription_id, array $params = []): array
    {
        $errors = [];
        if (empty($company_id)) {
            $errors[] = 'O ID da empresa é obrigatório';
        }
        if (empty($subscription_id)) {
            $errors[] = 'O ID da assinatura é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        $params[] = [
            'name' => 'company_id',
            'value' => $company_id
        ];

        $params[] = [
            'name' => 'subscription_id',
            'value' => $subscription_id
        ];

        try {
            $dados = $this->get("nfconta/subscriptions", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por cancelar uma assinatura na WFPay
     *
     * @param int $company_id ID da empresa que irá gerar cobrança
     * @param int $subscription_id ID da assinatura no WFPay
     * @param array $dados Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function cancelaAssinaturaArmazenamento(int $company_id, int $subscription_id, array $params = []): array
    {
        $errors = [];
        if (empty($company_id)) {
            $errors[] = 'O ID da empresa é obrigatório';
        }
        if (empty($subscription_id)) {
            $errors[] = 'O ID da assinatura é obrigatório';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        $params[] = [
            'name' => 'company_id',
            'value' => $company_id
        ];

        try {
            $dados = $this->delete("nfconta/subscriptions/$subscription_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por remover um evento crítico na NFConta
     *
     * @param int $critical_event_id ID do evento crítico
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function cancelaEventoCritico(int $company_id, int $critical_event_id, array $params = []): array
    {
        try {

            if (empty($company_id)) {
                $errors[] = 'O ID da empresa é obrigatório';
            }

            if (empty($critical_event_id)) {
                $errors[] = 'O ID do evento crítico é obrigatório';
            }

            if (!empty($errors)) {
                throw new Exception(implode("\r\n", $errors), 1);
            }

            $params[] = [
                'name' => 'company_id',
                'value' => $company_id
            ];

            $dados = $this->delete("nfconta/critical-event/$critical_event_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por buscar uma cobrança na NFConta pelo WFPay
     *
     * @param int $company_id ID da empresa que irá buscar a cobrança
     * @param int $wfpay_installment_id ID da cobrança no wfpay
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function buscaCobrancaNFHubWfpay(int $company_id, int $wfpay_installment_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }
            $dados = $this->get("nfconta/charges/$wfpay_installment_id", $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função resposável por gerar uma cobrança na NFConta pelo WFPay
     *
     * @param int $company_id ID da empresa que irá gerar a cobrança
     * @param array $data Dados da requisição
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function geraCobrancaContratoNFHubWfpay(int $company_id, array $data, array $params = []): array
    {
        try {
            $params[] = [
                'name' => 'company_id',
                'value' => $company_id
            ];

            $dados = $this->post("nfconta/installments", $data, $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }
}
