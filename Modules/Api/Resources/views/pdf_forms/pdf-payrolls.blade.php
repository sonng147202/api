<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>

	<style>
		* {
			padding: 0;
			margin: 0;
			font-size: 14px;
		}
		.info span {
			width: 200px;
		}
		.font-weight-bold {
			font-weight: bold;
		}
		.text-center {
			text-align: center;
		}
		table {
			border-collapse: collapse;
			width: 100%;
			margin-top: 20px;
		}
		.table td, .table th {
			border: 1px solid;
			padding: 8px;
		}
		.text-right {
			text-align: right
		}
		.note {
		 	margin-top: 30px;
		}
		img {
			width: 200px;
		}

	</style>
</head>
<body>
	<header>
		<table>
			<tr>
				<td><img src="img/logo-medici.png" alt="" class="logo"></td>
				<td class="text-center">
					<p style="font-size: 18px">CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI</p>
					<p style="font-size: 18px; text-transform: uppercase;">BẢNG KÊ THU NHẬP THÀNH VIÊN</p>
					<p style="font-size: 18px; text-transform: uppercase;">{{ $cycles }}</p>
				</td>
			</tr>
			
		</table>

		<table>
			<tr>
				<td width="auto" style="vertical-align: top;">Họ và tên: {{ $name }}</td>
				<td width="auto" style="vertical-align: top;">Vị trí: {{ $level }}</td>
			</tr>
			<tr>
				<td width="auto" style="vertical-align: top;">Mã Medici: {{ $code }}</td>
				<td width="auto" style="vertical-align: top;">Văn phòng: {{ $office }}</td>
			</tr>
			<tr>
				<td colspan="2" class="text-right">Đơn vị tính: VNĐ</td>
			</tr>
		</table>

		@php
			$total_income_before_tax = $total_personal_income_before_tax + $total_branch_income_before_tax + $total_peer_income_before_tax + $same_level_income + $cash_received_before_tax;
			$total_income_after_tax = $total_personal_income_after_tax + $total_branch_income_after_tax + $total_peer_income_after_tax + $same_level_income + $cash_received_before_tax * 0.9;
		@endphp
		<table class="table">
			<tr>
				<th>STT</th>
				<th>THÔNG TIN THU NHẬP</th>
				<th>DOANH SỐ P.FYP</th>
				<th>SỐ TIỀN</th>
			</tr>
			<tr>
				<td class="text-center">1</td>
				<td>Thu nhập cá nhân</td>
				<td class="text-right">{{ number_format($total_personal_revenue, 0, ',', '.') }}</td>
				<td class="text-right">{{ number_format($total_personal_income_before_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td class="text-center">2</td>
				<td>Thu nhập hệ thống</td>
				<td class="text-right">{{ number_format($total_branch_revenue, 0, ',', '.') }}</td>
				<td class="text-right">{{ number_format($total_branch_income_before_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td class="text-center">3</td>
				<td>Thu nhập đồng cấp</td>
				<td class="text-right">{{ number_format($total_peer_revenue, 0, ',', '.') }}</td>
				<td class="text-right">{{ number_format($total_peer_income_before_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td class="text-center">4</td>
				<td>Thu nhập đồng hưởng</td>
				<td class="text-right">{{ number_format($same_level_income, 0, ',', '.') }}</td>
				<td class="text-right">{{ number_format($same_level_income, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td class="text-center">5</td>
				<td>Hỗ trợ khởi nghiệp</td>
				<td class="text-right">{{ number_format($p_fyp_agency_startup_support, 0, ',', '.') }}</td>
				<td class="text-right">{{ number_format($cash_received_before_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td colspan="3">TỔNG THU NHẬP</td>
				<td class="text-right">{{ number_format($total_income_before_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td colspan="3">THUẾ</td>
				<td class="text-right">{{ number_format($total_income_before_tax - $total_income_after_tax, 0, ',', '.') }}</td>
			</tr>
			<tr>
				<td colspan="3" class="font-weight-bold">TỔNG THU NHẬP SAU THUẾ</td>
				<td class="font-weight-bold text-right">{{ number_format($total_income_after_tax, 0, ',', '.') }}</td>
			</tr>
		</table>

		<p class="note">GHI CHÚ: KHOẢN THU NHẬP SẼ ĐƯỢC THANH TOÁN VÀO TÀI KHOẢN ANH/CHỊ ĐÃ ĐĂNG KÝ VỚI MEDICI VÀ THEO LỊCH CHI TRẢ CỦA CÔNG TY.</p>
	</header>
</body>
</html>