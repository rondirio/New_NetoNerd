import React, { useState } from 'react';
import { Printer, Plus, Trash2 } from 'lucide-react';

export default function OrdemServico() {
  const [os, setOs] = useState({
    numero: '001',
    dataEntrada: new Date().toISOString().split('T')[0],
    dataEntrega: '',
    prazo: '',
    nomeCliente: '',
    telefone: '',
    email: '',
    modeloComputador: '',
    processador: '',
    memRam: '',
    memSecundaria: '',
    servicoPedido: '',
    servicoFeito: '',
    conclusao: '',
    valor: ''
  });

  const [historico, setHistorico] = useState([]);

  const handleChange = (e) => {
    setOs({ ...os, [e.target.name]: e.target.value });
  };

  const salvarOS = () => {
    if (!os.nomeCliente || !os.servicoPedido) {
      alert('Preencha pelo menos o nome do cliente e o serviço pedido!');
      return;
    }
    setHistorico([...historico, { ...os, id: Date.now() }]);
    alert('OS salva com sucesso!');
  };

  const novaOS = () => {
    const novoNumero = String(parseInt(os.numero) + 1).padStart(3, '0');
    setOs({
      numero: novoNumero,
      dataEntrada: new Date().toISOString().split('T')[0],
      dataEntrega: '',
      prazo: '',
      nomeCliente: '',
      telefone: '',
      email: '',
      modeloComputador: '',
      processador: '',
      memRam: '',
      memSecundaria: '',
      servicoPedido: '',
      servicoFeito: '',
      conclusao: '',
      valor: ''
    });
  };

  const imprimir = () => {
    window.print();
  };

  const removerOS = (id) => {
    setHistorico(historico.filter(item => item.id !== id));
  };

  return (
    <div className="min-h-screen bg-gray-100 p-4">
      <div className="max-w-4xl mx-auto">
        <div className="bg-white rounded-lg shadow-lg p-8 mb-6 print:shadow-none">
          {/* Cabeçalho */}
          <div className="border-b-2 border-gray-800 pb-4 mb-6">
            <h1 className="text-3xl font-bold text-gray-800">NetoNerd</h1>
            <p className="text-sm text-gray-600">CNPJ: 51.243.583/0001-12</p>
            <p className="text-sm text-gray-600">Assistência Técnica em Informática</p>
          </div>

          <div className="flex justify-between items-center mb-6">
            <h2 className="text-2xl font-bold text-gray-800">Ordem de Serviço</h2>
            <div className="text-right">
              <p className="text-sm text-gray-600">Nº OS: <span className="font-bold text-lg">{os.numero}</span></p>
            </div>
          </div>

          {/* Datas e Prazos */}
          <div className="grid grid-cols-3 gap-4 mb-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">Data de Entrada</label>
              <input
                type="date"
                name="dataEntrada"
                value={os.dataEntrada}
                onChange={handleChange}
                className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">Prazo de Entrega</label>
              <input
                type="date"
                name="prazo"
                value={os.prazo}
                onChange={handleChange}
                className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">Data de Entrega</label>
              <input
                type="date"
                name="dataEntrega"
                value={os.dataEntrega}
                onChange={handleChange}
                className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          {/* Dados do Cliente */}
          <div className="mb-6">
            <h3 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Dados do Cliente</h3>
            <div className="grid grid-cols-1 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">Nome Completo</label>
                <input
                  type="text"
                  name="nomeCliente"
                  value={os.nomeCliente}
                  onChange={handleChange}
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="Nome do cliente"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">Telefone</label>
                  <input
                    type="tel"
                    name="telefone"
                    value={os.telefone}
                    onChange={handleChange}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="(00) 00000-0000"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">E-mail</label>
                  <input
                    type="email"
                    name="email"
                    value={os.email}
                    onChange={handleChange}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="email@exemplo.com"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Especificações do Equipamento */}
          <div className="mb-6">
            <h3 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Especificações do Equipamento</h3>
            <div className="grid grid-cols-1 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">Modelo do Computador</label>
                <input
                  type="text"
                  name="modeloComputador"
                  value={os.modeloComputador}
                  onChange={handleChange}
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="Ex: Dell Inspiron 15, Notebook Acer Aspire 5"
                />
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">Processador</label>
                  <input
                    type="text"
                    name="processador"
                    value={os.processador}
                    onChange={handleChange}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: Intel i5 10ª Gen"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">Memória RAM</label>
                  <input
                    type="text"
                    name="memRam"
                    value={os.memRam}
                    onChange={handleChange}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: 8GB DDR4"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">Armazenamento</label>
                  <input
                    type="text"
                    name="memSecundaria"
                    value={os.memSecundaria}
                    onChange={handleChange}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: SSD 256GB"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Serviços */}
          <div className="mb-6">
            <h3 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Serviços</h3>
            <div className="grid grid-cols-1 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">Serviço Solicitado</label>
                <textarea
                  name="servicoPedido"
                  value={os.servicoPedido}
                  onChange={handleChange}
                  rows="3"
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="Descreva o problema relatado pelo cliente..."
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">Serviço Realizado</label>
                <textarea
                  name="servicoFeito"
                  value={os.servicoFeito}
                  onChange={handleChange}
                  rows="3"
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="Descreva os serviços realizados..."
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">Conclusão/Observações</label>
                <textarea
                  name="conclusao"
                  value={os.conclusao}
                  onChange={handleChange}
                  rows="2"
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="Observações finais, recomendações, etc..."
                />
              </div>
              <div className="w-1/3">
                <label className="block text-sm font-semibold text-gray-700 mb-1">Valor do Serviço (R$)</label>
                <input
                  type="text"
                  name="valor"
                  value={os.valor}
                  onChange={handleChange}
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  placeholder="0,00"
                />
              </div>
            </div>
          </div>

          {/* Assinaturas */}
          <div className="grid grid-cols-2 gap-8 mt-8 pt-6 border-t-2">
            <div className="text-center">
              <div className="border-t-2 border-gray-800 pt-2 mt-16">
                <p className="font-semibold">Técnico Responsável</p>
              </div>
            </div>
            <div className="text-center">
              <div className="border-t-2 border-gray-800 pt-2 mt-16">
                <p className="font-semibold">Cliente</p>
              </div>
            </div>
          </div>

          {/* Botões de Ação */}
          <div className="flex gap-3 mt-6 print:hidden">
            <button
              onClick={salvarOS}
              className="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
            >
              <Plus size={20} />
              Salvar OS
            </button>
            <button
              onClick={novaOS}
              className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
              Nova OS
            </button>
            <button
              onClick={imprimir}
              className="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition"
            >
              <Printer size={20} />
              Imprimir
            </button>
          </div>
        </div>

        {/* Histórico de OS */}
        {historico.length > 0 && (
          <div className="bg-white rounded-lg shadow-lg p-6 print:hidden">
            <h3 className="text-xl font-bold text-gray-800 mb-4">Histórico de Ordens de Serviço</h3>
            <div className="space-y-3">
              {historico.map((item) => (
                <div key={item.id} className="border border-gray-300 rounded p-4 flex justify-between items-start">
                  <div>
                    <p className="font-bold">OS Nº {item.numero} - {item.nomeCliente}</p>
                    <p className="text-sm text-gray-600">{item.modeloComputador}</p>
                    <p className="text-sm text-gray-600">Entrada: {item.dataEntrada}</p>
                    <p className="text-sm">{item.servicoPedido}</p>
                  </div>
                  <button
                    onClick={() => removerOS(item.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    <Trash2 size={20} />
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}